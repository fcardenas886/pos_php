<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$user = currentUser();
$message = '';
$error = '';

// Procesar formulario de guardar cliente o registrar abono
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'save_client';

    if ($action === 'save_client') {
        $nombre = trim($_POST['nombre'] ?? '');
        $rutCuerpo = (int)($_POST['rut_cuerpo'] ?? 0);
        $rutDv = strtoupper(trim($_POST['rut_dv'] ?? 'K'));
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $limiteCredito = (int)($_POST['limite_credito'] ?? 50000);

        if (!empty($nombre)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO clientes (RutCuerpo, RutDv, Nombre, Telefono, Email, LimiteCredito, SaldoDeudor)
                    VALUES (:rut, :dv, :nombre, :tel, :email, :limite, 0)
                ");
                $stmt->execute([
                    ':rut' => $rutCuerpo ?: null,
                    ':dv' => $rutCuerpo ? $rutDv : null,
                    ':nombre' => $nombre,
                    ':tel' => $telefono,
                    ':email' => $email,
                    ':limite' => $limiteCredito
                ]);
                $message = 'Cliente registrado exitosamente.';
            } catch (Exception $e) {
                $error = 'Error al registrar cliente: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'abono') {
        $clienteID = (int)($_POST['cliente_id'] ?? 0);
        $montoAbono = (int)($_POST['monto_abono'] ?? 0);
        $metodoPago = $_POST['metodo_pago'] ?? 'Efectivo';
        $concepto = trim($_POST['concepto'] ?? 'Abono a deuda de cliente');

        if ($clienteID > 0 && $montoAbono > 0) {
            try {
                $pdo->beginTransaction();

                // 1. Reducir saldo deudor del cliente
                $stmtUpd = $pdo->prepare("
                    UPDATE clientes SET SaldoDeudor = GREATEST(0, COALESCE(SaldoDeudor, 0) - :monto) WHERE ClienteID = :cid
                ");
                $stmtUpd->execute([':monto' => $montoAbono, ':cid' => $clienteID]);

                // 2. Registrar movimiento de caja como INGRESO en el turno activo si existe
                $stmtTurno = $pdo->prepare("SELECT TurnoID FROM turnos WHERE UsuarioID = :uid AND Estado = 'Abierto' ORDER BY TurnoID DESC LIMIT 1");
                $stmtTurno->execute([':uid' => $user['id']]);
                $turno = $stmtTurno->fetch();
                $turnoID = $turno ? $turno['TurnoID'] : null;

                if ($turno) {
                    $stmtMov = $pdo->prepare("
                        INSERT INTO movimientoscaja (TurnoID, TipoMovimiento, Monto, Descripcion)
                        VALUES (:tid, 'INGRESO', :monto, :desc)
                    ");
                    $stmtMov->execute([
                        ':tid' => $turnoID,
                        ':monto' => $montoAbono,
                        ':desc' => "ABONO CLIENTE: $concepto"
                    ]);
                }

                // 3. Aplicar el abono a las ventas al fiado más antiguas primero (FIFO),
                // para que el estado de cuenta sepa cuál venta quedó pagada y cuál no.
                $stmtDeudas = $pdo->prepare("
                    SELECT v.VentaID, pv.Monto AS MontoCredito,
                           COALESCE((SELECT SUM(ac.Monto) FROM abonoscredito ac WHERE ac.VentaID = v.VentaID), 0) AS YaAbonado
                    FROM ventas v
                    JOIN pagosventa pv ON v.VentaID = pv.VentaID AND pv.MetodoPago = 'Credito Interno'
                    WHERE v.ClienteID = :cid AND v.Estado = 'Completada'
                    ORDER BY v.VentaID ASC
                    FOR UPDATE
                ");
                $stmtDeudas->execute([':cid' => $clienteID]);
                $deudas = $stmtDeudas->fetchAll();

                $stmtAbono = $pdo->prepare("
                    INSERT INTO abonoscredito (ClienteID, FechaAbono, Monto, MetodoPago, TurnoID, VentaID)
                    VALUES (:cid, NOW(), :monto, :metodo, :tid, :vid)
                ");

                $restante = $montoAbono;
                foreach ($deudas as $d) {
                    if ($restante <= 0) break;
                    $pendiente = (int)$d['MontoCredito'] - (int)$d['YaAbonado'];
                    if ($pendiente <= 0) continue;
                    $aplicar = min($pendiente, $restante);
                    $stmtAbono->execute([
                        ':cid' => $clienteID, ':monto' => $aplicar, ':metodo' => $metodoPago,
                        ':tid' => $turnoID, ':vid' => $d['VentaID']
                    ]);
                    $restante -= $aplicar;
                }

                // Si sobra abono sin deuda por venta que lo explique (ej. anticipo, o
                // desfase con el saldo global historico), se registra sin venta asociada.
                if ($restante > 0) {
                    $stmtAbono->execute([
                        ':cid' => $clienteID, ':monto' => $restante, ':metodo' => $metodoPago,
                        ':tid' => $turnoID, ':vid' => null
                    ]);
                }

                $pdo->commit();
                $message = "Abono de " . formatCLP($montoAbono) . " registrado correctamente al saldo del cliente.";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Error al registrar el abono: ' . $e->getMessage();
            }
        } else {
            $error = 'Ingresa un cliente válido y un monto de abono mayor a 0.';
        }
    }
}

// Obtener Clientes con su saldo deudor
$stmtC = $pdo->query("SELECT * FROM clientes WHERE Activo = TRUE ORDER BY Nombre ASC");
$clientes = $stmtC->fetchAll();

// Obtener los últimos 30 abonos de crédito registrados
try {
    $stmtA = $pdo->query("
        SELECT ac.*, c.Nombre AS ClienteNombre 
        FROM abonoscredito ac 
        JOIN clientes c ON ac.ClienteID = c.ClienteID 
        ORDER BY ac.AbonoID DESC LIMIT 30
    ");
    $abonos = $stmtA->fetchAll();
} catch (Exception $e) {
    $abonos = [];
}

include __DIR__ . '/views/clientes.view.php';
require_once __DIR__ . '/includes/footer.php';

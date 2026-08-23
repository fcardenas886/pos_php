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
                    INSERT INTO Clientes (RutCuerpo, RutDv, Nombre, Telefono, Email, LimiteCredito, SaldoDeudor)
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
        $concepto = trim($_POST['concepto'] ?? 'Abono a deuda de cliente');

        if ($clienteID > 0 && $montoAbono > 0) {
            try {
                $pdo->beginTransaction();

                // 1. Reducir saldo deudor del cliente
                $stmtUpd = $pdo->prepare("
                    UPDATE Clientes SET SaldoDeudor = GREATEST(0, COALESCE(SaldoDeudor, 0) - :monto) WHERE ClienteID = :cid
                ");
                $stmtUpd->execute([':monto' => $montoAbono, ':cid' => $clienteID]);

                // 2. Registrar movimiento de caja como INGRESO en el turno activo si existe
                $stmtTurno = $pdo->prepare("SELECT TurnoID FROM Turnos WHERE UsuarioID = :uid AND Estado = 'Abierto' ORDER BY TurnoID DESC LIMIT 1");
                $stmtTurno->execute([':uid' => $user['id']]);
                $turno = $stmtTurno->fetch();

                if ($turno) {
                    $stmtMov = $pdo->prepare("
                        INSERT INTO MovimientosCaja (TurnoID, TipoMovimiento, Monto, Descripcion)
                        VALUES (:tid, 'INGRESO', :monto, :desc)
                    ");
                    $stmtMov->execute([
                        ':tid' => $turno['TurnoID'],
                        ':monto' => $montoAbono,
                        ':desc' => "ABONO CLIENTE: $concepto"
                    ]);
                }

                $pdo->commit();
                $message = "Abono de " . formatCLP($montoAbono) . " registrado correctamente al saldo del cliente.";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Error al registrar the abono: ' . $e->getMessage();
            }
        } else {
            $error = 'Ingresa un cliente válido y un monto de abono mayor a 0.';
        }
    }
}

// Obtener Clientes con su saldo deudor
$stmtC = $pdo->query("SELECT * FROM Clientes WHERE Activo = TRUE ORDER BY Nombre ASC");
$clientes = $stmtC->fetchAll();

include __DIR__ . '/views/clientes.view.php';
require_once __DIR__ . '/includes/footer.php';

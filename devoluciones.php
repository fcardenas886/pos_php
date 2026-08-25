<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$user = currentUser();
$message = '';
$error = '';

// Procesar Devolución
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $ventaID = (int)($_POST['venta_id'] ?? 0);
    $productoInput = trim($_POST['producto_id'] ?? '');
    $cantidad = (float)($_POST['cantidad'] ?? 0);
    $metodosValidos = ['Efectivo', 'Tarjeta', 'Nota de Credito'];
    $metodoDevolucion = in_array($_POST['metodo_devolucion'] ?? '', $metodosValidos, true) ? $_POST['metodo_devolucion'] : 'Efectivo';
    $motivo = trim($_POST['motivo'] ?? 'Devolución de cliente');

    if ($ventaID > 0 && !empty($productoInput) && $cantidad > 0) {
        try {
            $pdo->beginTransaction();

            // Buscar el producto real por ID o por Código de Barras
            $stmtFindP = $pdo->prepare("SELECT ProductoID FROM productos WHERE ProductoID = :id OR CodigoBarras = :barcode LIMIT 1");
            $stmtFindP->execute([
                ':id' => (int)$productoInput,
                ':barcode' => $productoInput
            ]);
            $productoID = (int)$stmtFindP->fetchColumn();

            if ($productoID <= 0) {
                throw new Exception("El producto '$productoInput' no está registrado en el catálogo.");
            }

            // Verificar que la venta existe y no está anulada
            $stmtV = $pdo->prepare("SELECT VentaID, Estado FROM ventas WHERE VentaID = :vid FOR UPDATE");
            $stmtV->execute([':vid' => $ventaID]);
            $venta = $stmtV->fetch();

            if (!$venta || $venta['Estado'] === 'Anulada') {
                throw new Exception("La venta #$ventaID no es válida o está anulada.");
            }

            // Obtener precio unitario cobrado y cantidad vendida
            $stmtP = $pdo->prepare("SELECT PrecioUnitario, Cantidad FROM detalleventas WHERE VentaID = :vid AND ProductoID = :pid");
            $stmtP->execute([':vid' => $ventaID, ':pid' => $productoID]);
            $detalleVenta = $stmtP->fetch();
            $precioUnitario = (int)($detalleVenta['PrecioUnitario'] ?? 0);

            if (!$detalleVenta || $precioUnitario <= 0) {
                throw new Exception("El producto no pertenece a la venta #$ventaID.");
            }

            // No permitir devolver más unidades de las que realmente se vendieron
            $stmtYaDev = $pdo->prepare("
                SELECT COALESCE(SUM(dd.Cantidad), 0) FROM detalledevoluciones dd
                JOIN devoluciones d ON dd.DevolucionID = d.DevolucionID
                WHERE d.VentaID = :vid AND dd.ProductoID = :pid
                FOR UPDATE
            ");
            $stmtYaDev->execute([':vid' => $ventaID, ':pid' => $productoID]);
            $yaDevuelto = (float)$stmtYaDev->fetchColumn();
            $disponibleDevolucion = (float)$detalleVenta['Cantidad'] - $yaDevuelto;

            if ($cantidad > $disponibleDevolucion) {
                throw new Exception("Solo quedan $disponibleDevolucion unidades disponibles para devolver de este producto en la venta #$ventaID.");
            }

            $montoDevuelto = (int)round($cantidad * $precioUnitario);

            // Obtener turno si es reembolso en Efectivo
            $turnoID = null;
            if ($metodoDevolucion === 'Efectivo') {
                $stmtTurno = $pdo->prepare("SELECT TurnoID FROM turnos WHERE UsuarioID = :uid AND Estado = 'Abierto' ORDER BY TurnoID DESC LIMIT 1");
                $stmtTurno->execute([':uid' => $user['id']]);
                $turno = $stmtTurno->fetch();
                if (!$turno) {
                    throw new Exception("Debes tener un turno de caja abierto para poder reembolsar en Efectivo (sacar de caja).");
                }
                $turnoID = (int)$turno['TurnoID'];
            }

            // 1. Insertar en Devoluciones
            $stmtDev = $pdo->prepare("
                INSERT INTO devoluciones (VentaID, UsuarioID, MontoDevuelto, MetodoDevolucion, Motivo)
                VALUES (:vid, :uid, :monto, :metodo, :motivo)
            ");
            $stmtDev->execute([
                ':vid' => $ventaID,
                ':uid' => $user['id'],
                ':monto' => $montoDevuelto,
                ':metodo' => $metodoDevolucion,
                ':motivo' => $motivo
            ]);
            $devolucionID = $pdo->lastInsertId();

            // 2. Insertar DetalleDevoluciones
            $stmtDDev = $pdo->prepare("
                INSERT INTO detalledevoluciones (DevolucionID, ProductoID, Cantidad, MontoDevuelto)
                VALUES (:did, :pid, :cant, :monto)
            ");
            $stmtDDev->execute([
                ':did' => $devolucionID,
                ':pid' => $productoID,
                ':cant' => $cantidad,
                ':monto' => $montoDevuelto
            ]);

            // 3. Devolver stock e ingresar a Kardex (DEVOLUCION_VENTA)
            $stmtUpd = $pdo->prepare("UPDATE productos SET Stock = Stock + :cant WHERE ProductoID = :pid");
            $stmtUpd->execute([':cant' => $cantidad, ':pid' => $productoID]);

            $stmtK = $pdo->prepare("
                INSERT INTO kardex (ProductoID, TipoTransaccion, DevolucionID, CantidadEntrada, StockSaldo, ValorUnitario)
                SELECT :pid, 'DEVOLUCION_VENTA', :did, :cant, Stock, :val FROM productos WHERE ProductoID = :pid2
            ");
            $stmtK->execute([
                ':pid' => $productoID,
                ':did' => $devolucionID,
                ':cant' => $cantidad,
                ':val' => $precioUnitario,
                ':pid2' => $productoID
            ]);

            // 4. Registrar Egreso en Caja si es Efectivo
            if ($metodoDevolucion === 'Efectivo') {
                $stmtMov = $pdo->prepare("
                    INSERT INTO movimientoscaja (TurnoID, TipoMovimiento, Monto, Descripcion)
                    VALUES (:tid, 'EGRESO', :monto, :desc)
                ");
                $stmtMov->execute([
                    ':tid' => $turnoID,
                    ':monto' => $montoDevuelto,
                    ':desc' => "DEVOLUCION BOLETA #$ventaID: Reembolso Efectivo"
                ]);
            }

            // 5. Generar Vale de Devolución si es Nota de Crédito
            $codigoVale = null;
            if ($metodoDevolucion === 'Nota de Credito') {
                $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $codigoVale = 'VALE-' . substr(str_shuffle($caracteres), 0, 8);
                $stmtVale = $pdo->prepare("
                    INSERT INTO valesdevolucion (CodigoVale, MontoOriginal, MontoDisponible, Estado, VentaID)
                    VALUES (:codigo, :monto, :monto2, 'Activo', :vid)
                ");
                $stmtVale->execute([
                    ':codigo' => $codigoVale,
                    ':monto' => $montoDevuelto,
                    ':monto2' => $montoDevuelto,
                    ':vid' => $ventaID
                ]);

                if (!isset($_SESSION)) {
                    session_start();
                }
                $_SESSION['ultimo_vale'] = [
                    'codigo' => $codigoVale,
                    'monto' => $montoDevuelto,
                    'fecha' => date('d/m/Y H:i')
                ];
            }

            $pdo->commit();

            if ($codigoVale) {
                $message = "Devolución registrada exitosamente. Se ha generado la Nota de Crédito / Vale: <strong>$codigoVale</strong> por un monto de " . formatCLP($montoDevuelto) . ".";
            } else {
                $message = "Devolución registrada exitosamente (ID #$devolucionID). Se reembolsaron " . formatCLP($montoDevuelto) . " mediante $metodoDevolucion (egreso registrado en caja).";
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Error al registrar devolución: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor completa todos los campos requeridos.';
    }
}

// Historial de Devoluciones
$stmtHist = $pdo->query("
    SELECT d.*, u.Nombre AS Usuario, v.TipoDocumento,
           GROUP_CONCAT(CONCAT(p.Nombre, ' (x', dd.Cantidad, ')') SEPARATOR ', ') AS ItemsDevueltos
    FROM devoluciones d
    JOIN usuarios u ON d.UsuarioID = u.UsuarioID
    JOIN ventas v ON d.VentaID = v.VentaID
    LEFT JOIN detalledevoluciones dd ON d.DevolucionID = dd.DevolucionID
    LEFT JOIN productos p ON dd.ProductoID = p.ProductoID
    GROUP BY d.DevolucionID
    ORDER BY d.DevolucionID DESC LIMIT 20
");
$devoluciones = $stmtHist->fetchAll();

include __DIR__ . '/views/devoluciones.view.php';
require_once __DIR__ . '/includes/footer.php';

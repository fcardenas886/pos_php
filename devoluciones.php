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
    $productoID = (int)($_POST['producto_id'] ?? 0);
    $cantidad = (float)($_POST['cantidad'] ?? 0);
    $metodoDevolucion = $_POST['metodo_devolucion'] ?? 'Efectivo';
    $motivo = trim($_POST['motivo'] ?? 'Devolución de cliente');

    if ($ventaID > 0 && $productoID > 0 && $cantidad > 0) {
        try {
            $pdo->beginTransaction();

            // Verificar que la venta existe y no está anulada
            $stmtV = $pdo->prepare("SELECT VentaID, Estado FROM Ventas WHERE VentaID = :vid FOR UPDATE");
            $stmtV->execute([':vid' => $ventaID]);
            $venta = $stmtV->fetch();

            if (!$venta || $venta['Estado'] === 'Anulada') {
                throw new Exception("La venta #$ventaID no es válida o está anulada.");
            }

            // Obtener precio unitario cobrado y cantidad vendida
            $stmtP = $pdo->prepare("SELECT PrecioUnitario, Cantidad FROM DetalleVentas WHERE VentaID = :vid AND ProductoID = :pid");
            $stmtP->execute([':vid' => $ventaID, ':pid' => $productoID]);
            $detalleVenta = $stmtP->fetch();
            $precioUnitario = (int)($detalleVenta['PrecioUnitario'] ?? 0);

            if (!$detalleVenta || $precioUnitario <= 0) {
                throw new Exception("El producto no pertenece a la venta #$ventaID.");
            }

            // No permitir devolver más unidades de las que realmente se vendieron
            $stmtYaDev = $pdo->prepare("
                SELECT COALESCE(SUM(dd.Cantidad), 0) FROM DetalleDevoluciones dd
                JOIN Devoluciones d ON dd.DevolucionID = d.DevolucionID
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

            // 1. Insertar en Devoluciones
            $stmtDev = $pdo->prepare("
                INSERT INTO Devoluciones (VentaID, UsuarioID, MontoDevuelto, MetodoDevolucion, Motivo)
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
                INSERT INTO DetalleDevoluciones (DevolucionID, ProductoID, Cantidad, MontoDevuelto)
                VALUES (:did, :pid, :cant, :monto)
            ");
            $stmtDDev->execute([
                ':did' => $devolucionID,
                ':pid' => $productoID,
                ':cant' => $cantidad,
                ':monto' => $montoDevuelto
            ]);

            // 3. Devolver stock e ingresar a Kardex (DEVOLUCION_VENTA)
            $stmtUpd = $pdo->prepare("UPDATE Productos SET Stock = Stock + :cant WHERE ProductoID = :pid");
            $stmtUpd->execute([':cant' => $cantidad, ':pid' => $productoID]);

            $stmtK = $pdo->prepare("
                INSERT INTO Kardex (ProductoID, TipoTransaccion, DevolucionID, CantidadEntrada, StockSaldo, ValorUnitario)
                SELECT :pid, 'DEVOLUCION_VENTA', :did, :cant, Stock, :val FROM Productos WHERE ProductoID = :pid
            ");
            $stmtK->execute([
                ':pid' => $productoID,
                ':did' => $devolucionID,
                ':cant' => $cantidad,
                ':val' => $precioUnitario
            ]);

            $pdo->commit();
            $message = "Devolución registrada exitosamente (ID #$devolucionID). Se reembolsaron " . formatCLP($montoDevuelto) . " mediante $metodoDevolucion.";
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
    FROM Devoluciones d
    JOIN Usuarios u ON d.UsuarioID = u.UsuarioID
    JOIN Ventas v ON d.VentaID = v.VentaID
    LEFT JOIN DetalleDevoluciones dd ON d.DevolucionID = dd.DevolucionID
    LEFT JOIN Productos p ON dd.ProductoID = p.ProductoID
    GROUP BY d.DevolucionID
    ORDER BY d.DevolucionID DESC LIMIT 20
");
$devoluciones = $stmtHist->fetchAll();

include __DIR__ . '/views/devoluciones.view.php';
require_once __DIR__ . '/includes/footer.php';

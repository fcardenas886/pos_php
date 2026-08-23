<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$user = currentUser();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $productoID = (int)($_POST['producto_id'] ?? 0);
    $tipoMovimiento = $_POST['tipo_movimiento'] ?? 'ENTRADA'; // ENTRADA o SALIDA
    $cantidad = (float)($_POST['cantidad'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? 'Ajuste de inventario');

    if ($productoID > 0 && $cantidad > 0) {
        try {
            $pdo->beginTransaction();

            // 1. Crear AjusteStock
            $stmtA = $pdo->prepare("INSERT INTO AjustesStock (UsuarioID, Motivo) VALUES (:uid, :motivo)");
            $stmtA->execute([':uid' => $user['id'], ':motivo' => $motivo]);
            $ajusteID = $pdo->lastInsertId();

            // 2. Crear DetalleAjustesStock
            $stmtDA = $pdo->prepare("
                INSERT INTO DetalleAjustesStock (AjusteStockID, ProductoID, Cantidad, TipoMovimiento)
                VALUES (:aid, :pid, :cant, :tipo)
            ");
            $stmtDA->execute([':aid' => $ajusteID, ':pid' => $productoID, ':cant' => $cantidad, ':tipo' => $tipoMovimiento]);

            // 3. Actualizar Stock y registrar Kardex
            if ($tipoMovimiento === 'ENTRADA') {
                $stmtUpd = $pdo->prepare("UPDATE Productos SET Stock = Stock + :cant WHERE ProductoID = :pid");
                $stmtK = $pdo->prepare("
                    INSERT INTO Kardex (ProductoID, TipoTransaccion, AjusteStockID, CantidadEntrada, StockSaldo, ValorUnitario)
                    SELECT :pid, 'AJUSTE_ENTRADA', :aid, :cant, Stock, PrecioVenta FROM Productos WHERE ProductoID = :pid
                ");
            } else {
                $stmtUpd = $pdo->prepare("UPDATE Productos SET Stock = GREATEST(0, Stock - :cant) WHERE ProductoID = :pid");
                $stmtK = $pdo->prepare("
                    INSERT INTO Kardex (ProductoID, TipoTransaccion, AjusteStockID, CantidadSalida, StockSaldo, ValorUnitario)
                    SELECT :pid, 'AJUSTE_SALIDA', :aid, :cant, Stock, PrecioVenta FROM Productos WHERE ProductoID = :pid
                ");
            }

            $stmtUpd->execute([':cant' => $cantidad, ':pid' => $productoID]);
            $stmtK->execute([':pid' => $productoID, ':aid' => $ajusteID, ':cant' => $cantidad]);

            $pdo->commit();
            $message = "Ajuste de $tipoMovimiento por $cantidad unidades registrado correctamente.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Error al realizar el ajuste: ' . $e->getMessage();
        }
    } else {
        $error = 'Selecciona un producto y una cantidad válida.';
    }
}

$productosList = $pdo->query("SELECT ProductoID, Nombre, Stock FROM Productos WHERE Activo = TRUE ORDER BY Nombre ASC")->fetchAll();

$stmtHist = $pdo->query("
    SELECT a.*, u.Nombre AS Usuario, da.Cantidad, da.TipoMovimiento, p.Nombre AS ProductoName
    FROM AjustesStock a
    JOIN Usuarios u ON a.UsuarioID = u.UsuarioID
    LEFT JOIN DetalleAjustesStock da ON a.AjusteStockID = da.AjusteStockID
    LEFT JOIN Productos p ON da.ProductoID = p.ProductoID
    ORDER BY a.AjusteStockID DESC LIMIT 20
");
$historialAjustes = $stmtHist->fetchAll();

include __DIR__ . '/views/ajustes.view.php';
require_once __DIR__ . '/includes/footer.php';

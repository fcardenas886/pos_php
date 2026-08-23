<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$user = currentUser();
$message = '';
$error = '';

// Procesar Formulario de Registro de Compra
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $proveedorID = (int)($_POST['proveedor_id'] ?? 1);
    $numeroDoc = trim($_POST['numero_documento'] ?? '');
    $productoID = (int)($_POST['producto_id'] ?? 0);
    $cantidad = (float)($_POST['cantidad'] ?? 0);
    $costoUnitario = (int)($_POST['costo_unitario'] ?? 0);

    if ($productoID > 0 && $cantidad > 0 && $costoUnitario >= 0) {
        try {
            $pdo->beginTransaction();

            $subtotal = (int)round($cantidad * $costoUnitario);
            $montoNeto = (int)round($subtotal / 1.19);
            $montoIva = $subtotal - $montoNeto;

            // 1. Insertar Compra
            $stmtC = $pdo->prepare("
                INSERT INTO Compras (ProveedorID, UsuarioID, NumeroDocumento, MontoNeto, MontoIva, MontoTotal, Estado)
                VALUES (:prov, :uid, :numdoc, :neto, :iva, :total, 'Completada')
            ");
            $stmtC->execute([
                ':prov' => $proveedorID,
                ':uid' => $user['id'],
                ':numdoc' => $numeroDoc,
                ':neto' => $montoNeto,
                ':iva' => $montoIva,
                ':total' => $subtotal
            ]);
            $compraID = $pdo->lastInsertId();

            // 2. Insertar DetalleCompra
            $stmtDC = $pdo->prepare("
                INSERT INTO DetalleCompras (CompraID, ProductoID, Cantidad, CostoUnitario, Subtotal)
                VALUES (:cid, :pid, :cant, :costo, :subtotal)
            ");
            $stmtDC->execute([
                ':cid' => $compraID,
                ':pid' => $productoID,
                ':cant' => $cantidad,
                ':costo' => $costoUnitario,
                ':subtotal' => $subtotal
            ]);

            // 3. Aumentar Stock y actualizar costo en Productos
            $stmtUpdP = $pdo->prepare("
                UPDATE Productos SET Stock = Stock + :cant, CostoCompra = :costo WHERE ProductoID = :pid
            ");
            $stmtUpdP->execute([':cant' => $cantidad, ':costo' => $costoUnitario, ':pid' => $productoID]);

            // 4. Registrar en Kardex COMPRA
            $stmtK = $pdo->prepare("
                INSERT INTO Kardex (ProductoID, TipoTransaccion, CompraID, CantidadEntrada, StockSaldo, ValorUnitario)
                SELECT :pid, 'COMPRA', :cid, :cant, Stock, :costo FROM Productos WHERE ProductoID = :pid
            ");
            $stmtK->execute([
                ':pid' => $productoID,
                ':cid' => $compraID,
                ':cant' => $cantidad,
                ':costo' => $costoUnitario
            ]);

            $pdo->commit();
            $message = 'Ingreso de mercadería registrado correctamente en Kardex.';
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Error al registrar la compra: ' . $e->getMessage();
        }
    } else {
        $error = 'Selecciona un producto, cantidad válida y costo unitario.';
    }
}

// Obtener Proveedores y Productos
$proveedores = $pdo->query("SELECT * FROM Proveedores WHERE Activo = TRUE ORDER BY RazonSocial ASC")->fetchAll();
$productos = $pdo->query("SELECT * FROM Productos WHERE Activo = TRUE ORDER BY Nombre ASC")->fetchAll();

// Obtener Historial de Compras
$stmtCompras = $pdo->query("
    SELECT c.*, p.RazonSocial AS Proveedor, u.Nombre AS Usuario,
           (SELECT GROUP_CONCAT(CONCAT(pr.Nombre, ' (x', dc.Cantidad, ')') SEPARATOR ', ') 
            FROM DetalleCompras dc JOIN Productos pr ON dc.ProductoID = pr.ProductoID 
            WHERE dc.CompraID = c.CompraID) AS Items
    FROM Compras c
    JOIN Proveedores p ON c.ProveedorID = p.ProveedorID
    JOIN Usuarios u ON c.UsuarioID = u.UsuarioID
    ORDER BY c.CompraID DESC LIMIT 20
");
$historialCompras = $stmtCompras->fetchAll();

include __DIR__ . '/views/compras.view.php';
require_once __DIR__ . '/includes/footer.php';

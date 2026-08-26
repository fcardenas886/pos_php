<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$user = currentUser();
$message = '';
$error = '';

// Obtener Proveedores y Productos para los selectores de la vista
$proveedores = $pdo->query("SELECT * FROM proveedores WHERE Activo = TRUE ORDER BY RazonSocial ASC")->fetchAll();
$productos = $pdo->query("SELECT * FROM productos WHERE Activo = TRUE ORDER BY Nombre ASC")->fetchAll();

// Notas de Pedido pendientes, para poder recibirlas directo desde aquí
$stmtNotasPendientes = $pdo->query("
    SELECT np.NotaPedidoID, np.NumeroDocumento, np.FechaPedido, p.RazonSocial AS Proveedor,
           (SELECT GROUP_CONCAT(CONCAT(pr.Nombre, ' (x', dnp.CantidadPedida, ')') SEPARATOR ', ')
            FROM detallenotaspedido dnp JOIN productos pr ON dnp.ProductoID = pr.ProductoID
            WHERE dnp.NotaPedidoID = np.NotaPedidoID) AS Items
    FROM notaspedido np
    JOIN proveedores p ON np.ProveedorID = p.ProveedorID
    WHERE np.Estado = 'Pendiente'
    ORDER BY np.NotaPedidoID DESC
");
$notasPendientes = $stmtNotasPendientes->fetchAll();

// Obtener Historial de Compras Recientes (con resumen de ítems agregados)
$stmtCompras = $pdo->query("
    SELECT c.*, p.RazonSocial AS Proveedor, u.Nombre AS Usuario,
           (SELECT GROUP_CONCAT(CONCAT(pr.Nombre, ' (x', dc.Cantidad, ')') SEPARATOR ', ') 
            FROM detallecompras dc JOIN productos pr ON dc.ProductoID = pr.ProductoID 
            WHERE dc.CompraID = c.CompraID) AS Items
    FROM compras c
    JOIN proveedores p ON c.ProveedorID = p.ProveedorID
    JOIN usuarios u ON c.UsuarioID = u.UsuarioID
    ORDER BY c.CompraID DESC LIMIT 20
");
$historialCompras = $stmtCompras->fetchAll();

include __DIR__ . '/views/compras.view.php';
require_once __DIR__ . '/includes/footer.php';

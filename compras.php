<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$user = currentUser();
$message = '';
$error = '';

// Obtener Proveedores y Productos para los selectores de la vista
$proveedores = $pdo->query("SELECT * FROM Proveedores WHERE Activo = TRUE ORDER BY RazonSocial ASC")->fetchAll();
$productos = $pdo->query("SELECT * FROM Productos WHERE Activo = TRUE ORDER BY Nombre ASC")->fetchAll();

// Obtener Historial de Compras Recientes (con resumen de ítems agregados)
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

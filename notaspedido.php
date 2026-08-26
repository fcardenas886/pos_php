<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$user = currentUser();

// Obtener Proveedores y Productos para los selectores de la vista
$proveedores = $pdo->query("SELECT * FROM proveedores WHERE Activo = TRUE ORDER BY RazonSocial ASC")->fetchAll();
$productos = $pdo->query("SELECT * FROM productos WHERE Activo = TRUE ORDER BY Nombre ASC")->fetchAll();

// Búsqueda: por N° de nota, proveedor o N° de documento
$q = trim($_GET['q'] ?? '');
$buscarPorID = ctype_digit($q) ? (int)$q : 0;

$stmtPendientes = $pdo->prepare("
    SELECT np.*, p.RazonSocial AS Proveedor, u.Nombre AS Usuario,
           (SELECT GROUP_CONCAT(CONCAT(pr.Nombre, ' (x', dnp.CantidadPedida, ')') SEPARATOR ', ')
            FROM detallenotaspedido dnp JOIN productos pr ON dnp.ProductoID = pr.ProductoID
            WHERE dnp.NotaPedidoID = np.NotaPedidoID) AS Items
    FROM notaspedido np
    JOIN proveedores p ON np.ProveedorID = p.ProveedorID
    JOIN usuarios u ON np.UsuarioID = u.UsuarioID
    WHERE np.Estado = 'Pendiente'
      AND (:q = '' OR np.NotaPedidoID = :id OR p.RazonSocial LIKE :like OR np.NumeroDocumento LIKE :like2)
    ORDER BY np.NotaPedidoID DESC
");
$stmtPendientes->execute([':q' => $q, ':id' => $buscarPorID, ':like' => "%$q%", ':like2' => "%$q%"]);
$notasPendientes = $stmtPendientes->fetchAll();

// Historial de Notas de Pedido ya recibidas o canceladas
$stmtHistorial = $pdo->prepare("
    SELECT np.*, p.RazonSocial AS Proveedor, u.Nombre AS Usuario,
           (SELECT GROUP_CONCAT(CONCAT(pr.Nombre, ' (x', dnp.CantidadPedida, ')') SEPARATOR ', ')
            FROM detallenotaspedido dnp JOIN productos pr ON dnp.ProductoID = pr.ProductoID
            WHERE dnp.NotaPedidoID = np.NotaPedidoID) AS Items
    FROM notaspedido np
    JOIN proveedores p ON np.ProveedorID = p.ProveedorID
    JOIN usuarios u ON np.UsuarioID = u.UsuarioID
    WHERE np.Estado != 'Pendiente'
      AND (:q = '' OR np.NotaPedidoID = :id OR p.RazonSocial LIKE :like OR np.NumeroDocumento LIKE :like2)
    ORDER BY np.NotaPedidoID DESC
    LIMIT " . ($q !== '' ? 100 : 20) . "
");
$stmtHistorial->execute([':q' => $q, ':id' => $buscarPorID, ':like' => "%$q%", ':like2' => "%$q%"]);
$historialNotas = $stmtHistorial->fetchAll();

include __DIR__ . '/views/notaspedido.view.php';
require_once __DIR__ . '/includes/footer.php';

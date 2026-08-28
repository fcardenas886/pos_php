<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$user = currentUser();
$message = '';
$error = '';

// Obtener lista de proveedores activos para el selector del modal
$proveedoresList = $pdo->query("SELECT ProveedorID, RazonSocial FROM proveedores WHERE Activo = TRUE ORDER BY RazonSocial ASC")->fetchAll();

$productosList = $pdo->query("SELECT ProductoID, Nombre, Stock FROM productos WHERE Activo = TRUE ORDER BY Nombre ASC")->fetchAll();

// Cargar historial de ajustes agrupados en una sola fila por AjusteStockID con GROUP_CONCAT
$stmtHist = $pdo->query("
    SELECT a.AjusteStockID, a.FechaAjuste, a.Motivo, a.DocReferencia, 
           u.Nombre AS Usuario, prov.RazonSocial AS Proveedor,
           GROUP_CONCAT(CONCAT(p.Nombre, ' (', da.TipoMovimiento, ' ', da.Cantidad, ')') SEPARATOR ', ') AS DetallesProductos
    FROM ajustesstock a
    JOIN usuarios u ON a.UsuarioID = u.UsuarioID
    LEFT JOIN proveedores prov ON a.ProveedorID = prov.ProveedorID
    LEFT JOIN detalleajustesstock da ON a.AjusteStockID = da.AjusteStockID
    LEFT JOIN productos p ON da.ProductoID = p.ProductoID
    GROUP BY a.AjusteStockID
    ORDER BY a.AjusteStockID DESC LIMIT 20
");
$historialAjustes = $stmtHist->fetchAll();

include __DIR__ . '/views/ajustes.view.php';
require_once __DIR__ . '/includes/footer.php';

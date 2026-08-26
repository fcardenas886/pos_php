<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$user = currentUser();
$message = '';
$error = '';

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

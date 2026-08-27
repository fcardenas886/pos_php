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

// Vales de Devolución / Notas de Crédito activos (con saldo disponible > 0)
$stmtVales = $pdo->query("
    SELECT v.*, vt.TipoDocumento, vt.TurnoID
    FROM valesdevolucion v
    LEFT JOIN ventas vt ON v.VentaID = vt.VentaID
    WHERE v.MontoDisponible > 0 AND v.Estado = 'Activo'
    ORDER BY v.ValeID DESC
");
$valesActivos = $stmtVales->fetchAll();

include __DIR__ . '/views/devoluciones.view.php';
require_once __DIR__ . '/includes/footer.php';

<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$user = currentUser();

// 1. Total Ventas Hoy
$stmtVentasHoy = $pdo->query("
    SELECT COUNT(*) as total_ventas, COALESCE(SUM(MontoTotal), 0) as total_monto 
    FROM Ventas 
    WHERE DATE(FechaVenta) = CURRENT_DATE() AND Estado = 'Completada'
");
$statsVentas = $stmtVentasHoy->fetch();

// 2. Productos bajo Stock Mínimo
$stmtLowStock = $pdo->query("
    SELECT COUNT(*) as bajo_stock 
    FROM Productos 
    WHERE Stock <= StockMinimo AND Activo = TRUE
");
$lowStockCount = $stmtLowStock->fetch()['bajo_stock'];

// 3. Estado del Turno Actual del usuario
$stmtTurno = $pdo->prepare("
    SELECT TurnoID, FechaApertura, MontoApertura, Estado 
    FROM Turnos 
    WHERE UsuarioID = :uid AND Estado = 'Abierto' 
    ORDER BY TurnoID DESC LIMIT 1
");
$stmtTurno->execute([':uid' => $user['id']]);
$turnoActivo = $stmtTurno->fetch();

// 4. Últimas Ventas
$stmtUltimas = $pdo->query("
    SELECT v.VentaID, v.FechaVenta, v.TipoDocumento, v.MontoTotal, v.Estado, u.Nombre AS Cajero
    FROM Ventas v
    JOIN Turnos t ON v.TurnoID = t.TurnoID
    JOIN Usuarios u ON t.UsuarioID = u.UsuarioID
    ORDER BY v.VentaID DESC LIMIT 5
");
$ultimasVentas = $stmtUltimas->fetchAll();

include __DIR__ . '/views/index.view.php';
require_once __DIR__ . '/includes/footer.php';

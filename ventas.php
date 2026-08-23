<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();

$fecha = $_GET['fecha'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT v.VentaID, v.FechaVenta, v.TipoDocumento, v.MontoNeto, v.MontoIva, v.MontoTotal, v.Estado, u.Nombre AS Cajero,
           GROUP_CONCAT(CONCAT(p.Nombre, ' (x', d.Cantidad, ')') SEPARATOR ', ') AS ProductosDetalle
    FROM Ventas v
    JOIN Turnos t ON v.TurnoID = t.TurnoID
    JOIN Usuarios u ON t.UsuarioID = u.UsuarioID
    LEFT JOIN DetalleVentas d ON v.VentaID = d.VentaID
    LEFT JOIN Productos p ON d.ProductoID = p.ProductoID
    WHERE DATE(v.FechaVenta) = :f
    GROUP BY v.VentaID
    ORDER BY v.VentaID DESC
");
$stmt->execute([':f' => $fecha]);
$ventas = $stmt->fetchAll();

include __DIR__ . '/views/ventas.view.php';
require_once __DIR__ . '/includes/footer.php';

<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();

$fechaInicio = $_GET['inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fin'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT 
        p.Nombre AS Producto,
        c.Nombre AS Categoria,
        SUM(dv.Cantidad) AS CantidadVendida,
        SUM(dv.Subtotal) AS TotalVentas,
        SUM(dv.Cantidad * p.CostoCompra) AS TotalCosto,
        SUM(dv.Subtotal - (dv.Cantidad * p.CostoCompra)) AS UtilidadEstimada
    FROM DetalleVentas dv
    JOIN Ventas v ON dv.VentaID = v.VentaID
    JOIN Productos p ON dv.ProductoID = p.ProductoID
    LEFT JOIN Categorias c ON p.CategoriaID = c.CategoriaID
    WHERE v.Estado = 'Completada' AND DATE(v.FechaVenta) BETWEEN :inicio AND :fin
    GROUP BY p.ProductoID
    ORDER BY UtilidadEstimada DESC
");
$stmt->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$reporte = $stmt->fetchAll();

$totalVentasMonto = 0;
$totalCostoMonto = 0;
$totalUtilidadMonto = 0;

foreach ($reporte as $r) {
    $totalVentasMonto += $r['TotalVentas'];
    $totalCostoMonto += $r['TotalCosto'];
    $totalUtilidadMonto += $r['UtilidadEstimada'];
}

include __DIR__ . '/views/reporte_utilidades.view.php';
require_once __DIR__ . '/includes/footer.php';

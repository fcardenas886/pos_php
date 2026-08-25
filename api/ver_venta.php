<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de venta no válido']);
    exit;
}

try {
    $pdo = getDB();
    
    // Obtener la cabecera de la venta
    $stmtV = $pdo->prepare("
        SELECT VentaID, FechaVenta, MontoTotal, MontoPagado, Vuelto, TipoDocumento, Estado 
        FROM ventas 
        WHERE VentaID = :id
    ");
    $stmtV->execute([':id' => $id]);
    $venta = $stmtV->fetch();

    if (!$venta) {
        echo json_encode(['success' => false, 'error' => 'Venta no encontrada']);
        exit;
    }

    // Obtener el detalle de los productos vendidos
    $stmtD = $pdo->prepare("
        SELECT dv.ProductoID, dv.Cantidad, dv.PrecioUnitario, dv.Descuento, dv.Subtotal, p.Nombre 
        FROM detalleventas dv
        JOIN productos p ON dv.ProductoID = p.ProductoID
        WHERE dv.VentaID = :id
    ");
    $stmtD->execute([':id' => $id]);
    $detalles = $stmtD->fetchAll();

    echo json_encode([
        'success' => true,
        'venta' => [
            'id' => $venta['VentaID'],
            'fecha' => date('d/m/Y H:i:s', strtotime($venta['FechaVenta'])),
            'total' => (int)$venta['MontoTotal'],
            'pagado' => (int)$venta['MontoPagado'],
            'vuelto' => (int)$venta['Vuelto'],
            'tipo_documento' => $venta['TipoDocumento'],
            'estado' => $venta['Estado']
        ],
        'detalles' => array_map(function($d) {
            return [
                'producto_id' => (int)$d['ProductoID'],
                'nombre' => $d['Nombre'],
                'cantidad' => (float)$d['Cantidad'],
                'precio' => (int)$d['PrecioUnitario'],
                'descuento' => (int)$d['Descuento'],
                'subtotal' => (int)$d['Subtotal']
            ];
        }, $detalles)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

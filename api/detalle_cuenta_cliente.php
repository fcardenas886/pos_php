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
    echo json_encode(['success' => false, 'error' => 'ID de cliente no válido']);
    exit;
}

try {
    $pdo = getDB();

    // Obtener información del cliente
    $stmtC = $pdo->prepare("SELECT Nombre, SaldoDeudor, LimiteCredito FROM clientes WHERE ClienteID = :cid");
    $stmtC->execute([':cid' => $id]);
    $cliente = $stmtC->fetch();

    if (!$cliente) {
        echo json_encode(['success' => false, 'error' => 'Cliente no encontrado']);
        exit;
    }

    // Obtener historial de deudas (ventas al fiado), con lo ya abonado a cada una
    $stmtDeudas = $pdo->prepare("
        SELECT v.VentaID, v.FechaVenta, v.MontoTotal, pv.Monto AS MontoCredito,
               COALESCE((SELECT SUM(ac.Monto) FROM abonoscredito ac WHERE ac.VentaID = v.VentaID), 0) AS Abonado
        FROM ventas v
        JOIN pagosventa pv ON v.VentaID = pv.VentaID
        WHERE v.ClienteID = :cid AND pv.MetodoPago = 'Credito Interno' AND v.Estado = 'Completada'
        ORDER BY v.VentaID DESC
    ");
    $stmtDeudas->execute([':cid' => $id]);
    $deudas = $stmtDeudas->fetchAll();

    // Obtener historial de abonos, con la venta a la que quedaron aplicados (si corresponde)
    $stmtAbonos = $pdo->prepare("
        SELECT AbonoID, FechaAbono, Monto, MetodoPago, VentaID
        FROM abonoscredito
        WHERE ClienteID = :cid
        ORDER BY AbonoID DESC
    ");
    $stmtAbonos->execute([':cid' => $id]);
    $abonos = $stmtAbonos->fetchAll();

    echo json_encode([
        'success' => true,
        'cliente' => [
            'nombre' => $cliente['Nombre'],
            'saldo_deudor' => (int)$cliente['SaldoDeudor'],
            'limite_credito' => (int)$cliente['LimiteCredito']
        ],
        'deudas' => array_map(function($d) {
            $pendiente = max(0, (int)$d['MontoCredito'] - (int)$d['Abonado']);
            $estado = $pendiente <= 0 ? 'Pagado' : (((int)$d['Abonado'] > 0) ? 'Parcial' : 'Pendiente');
            return [
                'venta_id' => $d['VentaID'],
                'fecha' => date('d/m/Y H:i', strtotime($d['FechaVenta'])),
                'total' => (int)$d['MontoTotal'],
                'credito' => (int)$d['MontoCredito'],
                'abonado' => (int)$d['Abonado'],
                'pendiente' => $pendiente,
                'estado' => $estado
            ];
        }, $deudas),
        'abonos' => array_map(function($a) {
            return [
                'abono_id' => $a['AbonoID'],
                'fecha' => date('d/m/Y H:i', strtotime($a['FechaAbono'])),
                'monto' => (int)$a['Monto'],
                'metodo' => $a['MetodoPago'],
                'venta_id' => $a['VentaID']
            ];
        }, $abonos)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

<?php
header('Content-Type: application/json; charset=utf-8');
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
    
    // 1. Obtener cabecera de la venta, con cajero y cliente
    $stmtV = $pdo->prepare("
        SELECT v.VentaID, v.TurnoID, v.ClienteID, v.FechaVenta, v.TipoDocumento, v.TipoDte, 
               v.Folio, v.MontoNeto, v.MontoIva, v.MontoExento, v.DescuentoGlobal, 
               v.MontoTotal, v.MontoPagado, v.Vuelto, v.PuntosGanados, v.PuntosCanjeados, 
               v.Estado, v.DtePdfPath,
               u.Nombre AS CajeroNombre, u.UsuarioID AS CajeroID,
               c.Nombre AS ClienteNombre, c.RutCuerpo, c.RutDv, c.Telefono AS ClienteTelefono, 
               c.Email AS ClienteEmail, c.LimiteCredito, c.SaldoDeudor
        FROM ventas v
        JOIN turnos t ON v.TurnoID = t.TurnoID
        JOIN usuarios u ON t.UsuarioID = u.UsuarioID
        LEFT JOIN clientes c ON v.ClienteID = c.ClienteID
        WHERE v.VentaID = :id
    ");
    $stmtV->execute([':id' => $id]);
    $venta = $stmtV->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        echo json_encode(['success' => false, 'error' => 'Venta no encontrada']);
        exit;
    }

    // 2. Obtener detalle de productos vendidos y cantidad ya devuelta
    $stmtD = $pdo->prepare("
        SELECT dv.ProductoID, dv.Cantidad, dv.FactorConversion, dv.PrecioUnitario, dv.CostoUnitario, dv.Descuento, dv.Subtotal, dv.EsAfecto,
               COALESCE(dv.NombreItem, p.Nombre) AS Nombre, p.Nombre AS NombreBase, p.CodigoBarras,
               COALESCE((
                   SELECT SUM(dd.Cantidad)
                   FROM detalledevoluciones dd
                   JOIN devoluciones d ON dd.DevolucionID = d.DevolucionID
                   WHERE d.VentaID = dv.VentaID AND dd.ProductoID = dv.ProductoID
               ), 0) AS CantidadYaDevuelta
        FROM detalleventas dv
        JOIN productos p ON dv.ProductoID = p.ProductoID
        WHERE dv.VentaID = :id
        ORDER BY dv.DetalleVentaID ASC
    ");
    $stmtD->execute([':id' => $id]);
    $detalles = $stmtD->fetchAll(PDO::FETCH_ASSOC);

    // 2b. Resumen para devoluciones: por producto y en unidades físicas (un pack de 3 cuenta 3),
    // sumando todas sus líneas. Las devoluciones se registran en estas mismas unidades.
    $devolucionPorProducto = [];
    foreach ($detalles as $d) {
        $pid = (int)$d['ProductoID'];
        $factor = (float)($d['FactorConversion'] ?? 1) > 0 ? (float)$d['FactorConversion'] : 1.0;
        if (!isset($devolucionPorProducto[$pid])) {
            $devolucionPorProducto[$pid] = [
                'producto_id' => $pid,
                'nombre' => $d['NombreBase'],
                'cantidad' => 0.0,
                'subtotal' => 0,
                'ya_devuelto' => (float)$d['CantidadYaDevuelta'],
            ];
        }
        $devolucionPorProducto[$pid]['cantidad'] += (float)$d['Cantidad'] * $factor;
        $devolucionPorProducto[$pid]['subtotal'] += (int)$d['Subtotal'];
    }
    foreach ($devolucionPorProducto as &$dp) {
        $dp['cantidad'] = round($dp['cantidad'], 3);
        $dp['disponible'] = max(0, round($dp['cantidad'] - $dp['ya_devuelto'], 3));
        // Precio realmente pagado por unidad (incluye promociones y precio de pack)
        $dp['precio'] = $dp['cantidad'] > 0 ? (int)round($dp['subtotal'] / $dp['cantidad']) : 0;
    }
    unset($dp);

    // 3. Obtener métodos de pago registrados para esta venta
    $stmtP = $pdo->prepare("
        SELECT MetodoPago, Monto 
        FROM pagosventa 
        WHERE VentaID = :id
        ORDER BY PagoVentaID ASC
    ");
    $stmtP->execute([':id' => $id]);
    $pagos = $stmtP->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay filas en pagosventa (ventas de prueba o legadas), armar un pago por defecto
    if (empty($pagos)) {
        $pagos = [
            [
                'MetodoPago' => 'Efectivo',
                'Monto' => (int)$venta['MontoTotal']
            ]
        ];
    }

    // 4. Buscar DTE emitido en dte_emitidos
    $stmtDte = $pdo->prepare("
        SELECT DteID, TipoDocumento, Folio, TrackID, EstadoSii, PdfUrl, XmlUrl, FechaEmision
        FROM dte_emitidos
        WHERE VentaID = :id
        ORDER BY DteID DESC
        LIMIT 1
    ");
    $stmtDte->execute([':id' => $id]);
    $dteRow = $stmtDte->fetch(PDO::FETCH_ASSOC);

    $dteData = null;
    if ($dteRow) {
        $dteData = [
            'success' => true,
            'folio' => $dteRow['Folio'],
            'tipo_documento' => $dteRow['TipoDocumento'],
            'track_id' => $dteRow['TrackID'],
            'estado_sii' => $dteRow['EstadoSii'],
            'pdf_url' => $dteRow['PdfUrl'],
            'xml_url' => $dteRow['XmlUrl'],
            'fecha_emision' => $dteRow['FechaEmision']
        ];
    } elseif (!empty($venta['DtePdfPath'])) {
        $dteData = [
            'success' => true,
            'folio' => $venta['Folio'] ?: $venta['VentaID'],
            'tipo_documento' => $venta['TipoDocumento'],
            'track_id' => null,
            'estado_sii' => 'Emitido',
            'pdf_url' => $venta['DtePdfPath'],
            'xml_url' => null,
            'fecha_emision' => $venta['FechaVenta']
        ];
    }

    // 5. Cargar datos de la empresa/local para encabezado y pie de ticket
    $stmtCfg = $pdo->query("
        SELECT Clave, Valor FROM configuraciones 
        WHERE Clave IN ('MINIMARKET_NOMBRE', 'MINIMARKET_RUT', 'MINIMARKET_DIRECCION', 'MINIMARKET_GIRO', 'MINIMARKET_TELEFONO', 'TICKET_PIE_PAGINA')
    ");
    $empresa = [
        'nombre' => 'MINIMARKET',
        'rut' => '',
        'direccion' => '',
        'giro' => '',
        'telefono' => '',
        'pie_pagina' => '¡Gracias por su preferencia!'
    ];
    while ($cfg = $stmtCfg->fetch(PDO::FETCH_ASSOC)) {
        if ($cfg['Clave'] === 'MINIMARKET_NOMBRE' && !empty($cfg['Valor'])) $empresa['nombre'] = $cfg['Valor'];
        if ($cfg['Clave'] === 'MINIMARKET_RUT') $empresa['rut'] = $cfg['Valor'];
        if ($cfg['Clave'] === 'MINIMARKET_DIRECCION') $empresa['direccion'] = $cfg['Valor'];
        if ($cfg['Clave'] === 'MINIMARKET_GIRO') $empresa['giro'] = $cfg['Valor'];
        if ($cfg['Clave'] === 'MINIMARKET_TELEFONO') $empresa['telefono'] = $cfg['Valor'];
        if ($cfg['Clave'] === 'TICKET_PIE_PAGINA' && !empty($cfg['Valor'])) $empresa['pie_pagina'] = $cfg['Valor'];
    }

    // 6. Preparar cliente formateado
    $clienteFormatted = null;
    if (!empty($venta['ClienteNombre'])) {
        $rutFmt = '';
        if (!empty($venta['RutCuerpo'])) {
            $rutFmt = number_format((int)$venta['RutCuerpo'], 0, '', '.') . '-' . $venta['RutDv'];
        }
        $limite = (int)($venta['LimiteCredito'] ?? 0);
        $saldo = (int)($venta['SaldoDeudor'] ?? 0);
        $cupoDisp = max(0, $limite - $saldo);

        $clienteFormatted = [
            'id' => (int)$venta['ClienteID'],
            'nombre' => $venta['ClienteNombre'],
            'rut' => $rutFmt,
            'telefono' => $venta['ClienteTelefono'] ?? '',
            'email' => $venta['ClienteEmail'] ?? '',
            'limite_credito' => $limite,
            'saldo_deudor' => $saldo,
            'cupo_disponible' => $cupoDisp
        ];
    }

    // 7. Preparar crédito si hubo pagos fiado/crédito
    $creditoInfo = null;
    $montoCredito = 0;
    foreach ($pagos as $p) {
        if (in_array($p['MetodoPago'], ['Credito', 'Fiado', 'Credito Interno'], true)) {
            $montoCredito += (int)$p['Monto'];
        }
    }
    if ($montoCredito > 0 && $clienteFormatted) {
        $creditoInfo = [
            'cliente' => $clienteFormatted['nombre'],
            'rut' => $clienteFormatted['rut'],
            'monto' => $montoCredito,
            'saldo_deudor' => $clienteFormatted['saldo_deudor'],
            'cupo_disponible' => $clienteFormatted['cupo_disponible']
        ];
    }

    // Respuesta JSON completa
    echo json_encode([
        'success' => true,
        'venta' => [
            'id' => (int)$venta['VentaID'],
            'turno_id' => (int)$venta['TurnoID'],
            'cajero' => $venta['CajeroNombre'],
            'fecha' => date('d/m/Y H:i:s', strtotime($venta['FechaVenta'])),
            'fecha_raw' => $venta['FechaVenta'],
            'tipo_documento' => $venta['TipoDocumento'],
            'tipo_dte' => $venta['TipoDte'],
            'folio' => $dteData['folio'] ?? ($venta['Folio'] ?: null),
            'monto_neto' => (int)$venta['MontoNeto'],
            'monto_iva' => (int)$venta['MontoIva'],
            'monto_exento' => (int)$venta['MontoExento'],
            'descuento_global' => (int)$venta['DescuentoGlobal'],
            'total' => (int)$venta['MontoTotal'],
            'pagado' => (int)$venta['MontoPagado'],
            'vuelto' => (int)$venta['Vuelto'],
            'puntos_ganados' => (int)$venta['PuntosGanados'],
            'puntos_canjeados' => (int)$venta['PuntosCanjeados'],
            'estado' => $venta['Estado'],
            'dte_pdf_path' => $venta['DtePdfPath']
        ],
        'cliente' => $clienteFormatted,
        'credito' => $creditoInfo,
        'dte' => $dteData,
        'empresa' => $empresa,
        'pagos' => array_map(function($p) {
            return [
                'metodo' => $p['MetodoPago'],
                'monto' => (int)$p['Monto']
            ];
        }, $pagos),
        'detalles' => array_map(function($d) {
            $disponible = max(0, (float)$d['Cantidad'] - (float)$d['CantidadYaDevuelta']);
            return [
                'producto_id' => (int)$d['ProductoID'],
                'nombre' => $d['Nombre'],
                'codigo_barra' => $d['CodigoBarras'] ?? '',
                'cantidad' => (float)$d['Cantidad'],
                'ya_devuelto' => (float)$d['CantidadYaDevuelta'],
                'disponible' => $disponible,
                'precio' => (int)$d['PrecioUnitario'],
                'costo' => (int)$d['CostoUnitario'],
                'descuento' => (int)$d['Descuento'],
                'subtotal' => (int)$d['Subtotal'],
                'es_afecto' => (bool)$d['EsAfecto']
            ];
        }, $detalles),
        'devolucion' => array_values($devolucionPorProducto)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

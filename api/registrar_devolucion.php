<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}
verifyCsrfApi();

$user = currentUser();
$input = json_decode(file_get_contents('php://input'), true);

$ventaID = (int)($input['venta_id'] ?? 0);
$itemsInput = $input['items'] ?? [];
$metodosValidos = ['Efectivo', 'Tarjeta', 'Nota de Credito', 'Cambio de Mercaderia'];
$metodoDevolucion = in_array($input['metodo_devolucion'] ?? '', $metodosValidos, true) ? $input['metodo_devolucion'] : 'Efectivo';
$motivo = trim($input['motivo'] ?? '') ?: 'Devolución de cliente';

if ($ventaID <= 0 || empty($itemsInput) || !is_array($itemsInput)) {
    echo json_encode(['success' => false, 'error' => 'Selecciona una boleta y al menos un producto a devolver.']);
    exit;
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();



    // Verificar que la venta existe y no está anulada
    $stmtV = $pdo->prepare("SELECT VentaID, Estado FROM ventas WHERE VentaID = :vid FOR UPDATE");
    $stmtV->execute([':vid' => $ventaID]);
    $venta = $stmtV->fetch();

    if (!$venta || $venta['Estado'] === 'Anulada') {
        throw new Exception("La venta #$ventaID no es válida o está anulada.");
    }

    // Sumar cantidades por producto (por si el cliente manda el mismo producto dos veces)
    $cantidadesPorProducto = [];
    foreach ($itemsInput as $it) {
        $pidRaw = trim((string)($it['producto_id'] ?? ''));
        $cant = (float)($it['cantidad'] ?? 0);
        if ($pidRaw === '' || $cant <= 0) {
            throw new Exception("Todos los productos deben tener una cantidad válida mayor a 0.");
        }
        $cantidadesPorProducto[$pidRaw] = ($cantidadesPorProducto[$pidRaw] ?? 0) + $cant;
    }

    $stmtFindP = $pdo->prepare("SELECT ProductoID FROM productos WHERE ProductoID = :id OR CodigoBarras = :barcode LIMIT 1");
    $stmtDetalleVenta = $pdo->prepare("SELECT PrecioUnitario, Cantidad, Subtotal FROM detalleventas WHERE VentaID = :vid AND ProductoID = :pid");
    $stmtYaDev = $pdo->prepare("
        SELECT COALESCE(SUM(dd.Cantidad), 0) FROM detalledevoluciones dd
        JOIN devoluciones d ON dd.DevolucionID = d.DevolucionID
        WHERE d.VentaID = :vid AND dd.ProductoID = :pid
        FOR UPDATE
    ");

    $itemsProcesados = [];
    $montoDevueltoTotal = 0;

    foreach ($cantidadesPorProducto as $productoInput => $cantidad) {
        $stmtFindP->execute([':id' => (int)$productoInput, ':barcode' => $productoInput]);
        $productoID = (int)$stmtFindP->fetchColumn();
        if ($productoID <= 0) {
            throw new Exception("El producto '$productoInput' no está registrado en el catálogo.");
        }

        $stmtDetalleVenta->execute([':vid' => $ventaID, ':pid' => $productoID]);
        $detalleVenta = $stmtDetalleVenta->fetch();
        $precioUnitario = (int)($detalleVenta['PrecioUnitario'] ?? 0);
        if (!$detalleVenta || $precioUnitario <= 0) {
            throw new Exception("El producto ID $productoID no pertenece a la venta #$ventaID.");
        }

        $stmtYaDev->execute([':vid' => $ventaID, ':pid' => $productoID]);
        $yaDevuelto = (float)$stmtYaDev->fetchColumn();
        $disponible = (float)$detalleVenta['Cantidad'] - $yaDevuelto;

        if ($cantidad > $disponible) {
            throw new Exception("Solo quedan $disponible unidades disponibles para devolver del producto ID $productoID en la venta #$ventaID.");
        }

        // Reembolsar lo realmente pagado por unidad (Subtotal/Cantidad), no el precio de
        // lista: si el producto tenía una promoción activa, PrecioUnitario no refleja
        // el descuento que ya se aplicó al cobrar.
        $precioEfectivo = (float)$detalleVenta['Subtotal'] / (float)$detalleVenta['Cantidad'];
        $montoItem = (int)round($cantidad * $precioEfectivo);
        $montoDevueltoTotal += $montoItem;

        $itemsProcesados[] = [
            'producto_id' => $productoID,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'monto' => $montoItem
        ];
    }

    // 1. Insertar cabecera de Devolución (una sola, por el total de todos los productos)
    $stmtDev = $pdo->prepare("
        INSERT INTO devoluciones (VentaID, UsuarioID, MontoDevuelto, MetodoDevolucion, Motivo)
        VALUES (:vid, :uid, :monto, :metodo, :motivo)
    ");
    $stmtDev->execute([
        ':vid' => $ventaID,
        ':uid' => $user['id'],
        ':monto' => $montoDevueltoTotal,
        ':metodo' => $metodoDevolucion,
        ':motivo' => $motivo
    ]);
    $devolucionID = $pdo->lastInsertId();

    // 2. Insertar un DetalleDevoluciones por producto, reponer stock y Kardex
    $stmtDDev = $pdo->prepare("
        INSERT INTO detalledevoluciones (DevolucionID, ProductoID, Cantidad, MontoDevuelto)
        VALUES (:did, :pid, :cant, :monto)
    ");
    $stmtUpdStock = $pdo->prepare("UPDATE productos SET Stock = Stock + :cant WHERE ProductoID = :pid");
    $stmtK = $pdo->prepare("
        INSERT INTO kardex (ProductoID, TipoTransaccion, DevolucionID, CantidadEntrada, StockSaldo, ValorUnitario)
        SELECT :pid, 'DEVOLUCION_VENTA', :did, :cant, Stock, :val FROM productos WHERE ProductoID = :pid2
    ");

    foreach ($itemsProcesados as $item) {
        $stmtDDev->execute([
            ':did' => $devolucionID,
            ':pid' => $item['producto_id'],
            ':cant' => $item['cantidad'],
            ':monto' => $item['monto']
        ]);
        $stmtUpdStock->execute([':cant' => $item['cantidad'], ':pid' => $item['producto_id']]);
        $stmtK->execute([
            ':pid' => $item['producto_id'],
            ':did' => $devolucionID,
            ':cant' => $item['cantidad'],
            ':val' => $item['precio_unitario'],
            ':pid2' => $item['producto_id']
        ]);
    }

    // 3. Generar un solo Vale si es Nota de Crédito, Efectivo (reembolso diferido), o Cambio de Mercadería
    $codigoVale = null;
    if ($metodoDevolucion === 'Nota de Credito' || $metodoDevolucion === 'Efectivo' || $metodoDevolucion === 'Cambio de Mercaderia') {
        $prefix = ($metodoDevolucion === 'Cambio de Mercaderia') ? 'TC-' : 'NC-';
        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codigoVale = $prefix . substr(str_shuffle($caracteres), 0, 8);
        
        $stmtVale = $pdo->prepare("
            INSERT INTO valesdevolucion (CodigoVale, MontoOriginal, MontoDisponible, Estado, VentaID)
            VALUES (:codigo, :monto, :monto2, 'Activo', :vid)
        ");
        $stmtVale->execute([
            ':codigo' => $codigoVale,
            ':monto' => $montoDevueltoTotal,
            ':monto2' => $montoDevueltoTotal,
            ':vid' => $ventaID
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'devolucion_id' => $devolucionID,
        'monto_total' => $montoDevueltoTotal,
        'cantidad_productos' => count($itemsProcesados),
        'metodo' => $metodoDevolucion,
        'vale_codigo' => $codigoVale
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

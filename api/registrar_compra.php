<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Control de acceso: solo Administradores y Supervisores
if (empty($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Administrador', 'Supervisor'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}
verifyCsrfApi();

$user = currentUser();
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['items']) || !is_array($input['items'])) {
    echo json_encode(['success' => false, 'error' => 'La lista de productos ingresados está vacía.']);
    exit;
}

$proveedorID = (int)($input['proveedor_id'] ?? 1);
$numeroDoc = trim($input['numero_documento'] ?? '');

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    // 1. Calcular totales de la compra
    $totalCompra = 0;
    $itemsProcesados = [];

    foreach ($input['items'] as $item) {
        $pid = (int)$item['producto_id'];
        $cant = (float)$item['cantidad'];
        $costo = (int)$item['costo_unitario'];

        if ($pid <= 0 || $cant <= 0 || $costo < 0) {
            throw new Exception("Datos de producto no válidos (ID: $pid, Cantidad: $cant, Costo: $costo)");
        }

        // Obtener datos actuales del producto para validar y retornar
        $stmtP = $pdo->prepare("SELECT ProductoID, Nombre, PrecioVenta, Stock FROM productos WHERE ProductoID = :pid FOR UPDATE");
        $stmtP->execute([':pid' => $pid]);
        $prod = $stmtP->fetch();

        if (!$prod) {
            throw new Exception("El producto ID $pid no existe en el catálogo.");
        }

        $subtotal = (int)round($cant * $costo);
        $totalCompra += $subtotal;

        $itemsProcesados[] = [
            'prod' => $prod,
            'cant' => $cant,
            'costo' => $costo,
            'subtotal' => $subtotal
        ];
    }

    $montoNeto = (int)round($totalCompra / 1.19);
    $montoIva = $totalCompra - $montoNeto;

    // 2. Insertar cabecera de Compra
    $stmtC = $pdo->prepare("
        INSERT INTO compras (ProveedorID, UsuarioID, NumeroDocumento, MontoNeto, MontoIva, MontoTotal, Estado)
        VALUES (:prov, :uid, :numdoc, :neto, :iva, :total, 'Completada')
    ");
    $stmtC->execute([
        ':prov' => $proveedorID,
        ':uid' => $user['id'],
        ':numdoc' => $numeroDoc,
        ':neto' => $montoNeto,
        ':iva' => $montoIva,
        ':total' => $totalCompra
    ]);
    $compraID = $pdo->lastInsertId();

    // 3. Registrar detalles de compra, actualizar stock, actualizar costo y registrar Kardex
    $stmtDC = $pdo->prepare("
        INSERT INTO detallecompras (CompraID, ProductoID, Cantidad, CostoUnitario, Subtotal)
        VALUES (:cid, :pid, :cant, :costo, :subtotal)
    ");

    $stmtUpdStock = $pdo->prepare("
        UPDATE productos SET Stock = Stock + :cant, CostoCompra = :costo WHERE ProductoID = :pid
    ");

    $stmtKardex = $pdo->prepare("
        INSERT INTO kardex (ProductoID, TipoTransaccion, CompraID, CantidadEntrada, StockSaldo, ValorUnitario)
        VALUES (:pid, 'COMPRA', :cid, :cant, :saldo, :val)
    ");

    $productosRetorno = [];

    foreach ($itemsProcesados as $item) {
        $p = $item['prod'];
        $cant = $item['cant'];
        $costo = $item['costo'];

        // Guardar detalle
        $stmtDC->execute([
            ':cid' => $compraID,
            ':pid' => $p['ProductoID'],
            ':cant' => $cant,
            ':costo' => $costo,
            ':subtotal' => $item['subtotal']
        ]);

        // Actualizar stock y costo del producto
        $stmtUpdStock->execute([
            ':cant' => $cant,
            ':costo' => $costo,
            ':pid' => $p['ProductoID']
        ]);

        // Registrar en Kardex
        $nuevoStock = $p['Stock'] + $cant;
        $stmtKardex->execute([
            ':pid' => $p['ProductoID'],
            ':cid' => $compraID,
            ':cant' => $cant,
            ':saldo' => $nuevoStock,
            ':val' => $costo
        ]);

        // Preparar objeto de retorno para ajuste de precios
        $productosRetorno[] = [
            'ProductoID' => $p['ProductoID'],
            'Nombre' => $p['Nombre'],
            'CostoCompra' => $costo,
            'PrecioVentaActual' => (int)$p['PrecioVenta']
        ];
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'compra_id' => $compraID,
        'monto_total' => $totalCompra,
        'productos' => $productosRetorno
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

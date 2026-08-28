<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$user = currentUser();

// Comprobar token CSRF por API
verifyCsrfApi();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Payload inválido']);
    exit;
}

$motivo = trim($data['motivo'] ?? 'Ajuste de inventario');
$esDevolucion = !empty($data['es_devolucion']);
$proveedorID = !empty($data['proveedor_id']) ? (int)$data['proveedor_id'] : null;
$docReferencia = !empty($data['doc_referencia']) ? trim($data['doc_referencia']) : null;
$items = $data['items'] ?? [];

if (empty($items)) {
    echo json_encode(['success' => false, 'error' => 'Debes agregar al menos un producto al ajuste.']);
    exit;
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    // 1. Crear cabecera AjusteStock
    $stmtA = $pdo->prepare("
        INSERT INTO ajustesstock (UsuarioID, Motivo, ProveedorID, DocReferencia) 
        VALUES (:uid, :motivo, :prov, :doc)
    ");
    $stmtA->execute([
        ':uid' => $user['id'],
        ':motivo' => $motivo,
        ':prov' => $proveedorID,
        ':doc' => $docReferencia
    ]);
    $ajusteID = $pdo->lastInsertId();

    // Preparar sentencias
    $stmtDA = $pdo->prepare("
        INSERT INTO detalleajustesstock (AjusteStockID, ProductoID, Cantidad, TipoMovimiento)
        VALUES (:aid, :pid, :cant, :tipo)
    ");
    
    $stmtAddStock = $pdo->prepare("UPDATE productos SET Stock = Stock + :cant WHERE ProductoID = :pid");
    $stmtSubStock = $pdo->prepare("UPDATE productos SET Stock = GREATEST(0, Stock - :cant) WHERE ProductoID = :pid");
    
    $stmtKardexEntrada = $pdo->prepare("
        INSERT INTO kardex (ProductoID, TipoTransaccion, AjusteStockID, CantidadEntrada, StockSaldo, ValorUnitario)
        SELECT :pid, 'AJUSTE_ENTRADA', :aid, :cant, Stock, PrecioVenta FROM productos WHERE ProductoID = :pid2
    ");
    
    $stmtKardexSalida = $pdo->prepare("
        INSERT INTO kardex (ProductoID, TipoTransaccion, AjusteStockID, CantidadSalida, StockSaldo, ValorUnitario)
        SELECT :pid, 'AJUSTE_SALIDA', :aid, :cant, Stock, PrecioVenta FROM productos WHERE ProductoID = :pid2
    ");

    foreach ($items as $item) {
        $productoID = (int)$item['producto_id'];
        $cantidad = (float)$item['cantidad'];
        $tipo = $esDevolucion ? 'SALIDA' : (($item['tipo_movimiento'] ?? '') === 'SALIDA' ? 'SALIDA' : 'ENTRADA');

        if ($productoID <= 0 || $cantidad <= 0) {
            throw new Exception("Cantidad o Producto inválido.");
        }

        // Insertar detalle
        $stmtDA->execute([
            ':aid' => $ajusteID,
            ':pid' => $productoID,
            ':cant' => $cantidad,
            ':tipo' => $tipo
        ]);

        // Actualizar stock y registrar en Kardex
        if ($tipo === 'ENTRADA') {
            $stmtAddStock->execute([':cant' => $cantidad, ':pid' => $productoID]);
            $stmtKardexEntrada->execute([
                ':pid' => $productoID,
                ':aid' => $ajusteID,
                ':cant' => $cantidad,
                ':pid2' => $productoID
            ]);
        } else {
            $stmtSubStock->execute([':cant' => $cantidad, ':pid' => $productoID]);
            $stmtKardexSalida->execute([
                ':pid' => $productoID,
                ':aid' => $ajusteID,
                ':cant' => $cantidad,
                ':pid2' => $productoID
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'ajuste_id' => $ajusteID]);

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

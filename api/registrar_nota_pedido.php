<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Administrador', 'Supervisor'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}
verifyCsrfApi();

$user = currentUser();
$input = json_decode(file_get_contents('php://input'), true);

$proveedorID = (int)($input['proveedor_id'] ?? 0);
$numeroDoc = trim($input['numero_documento'] ?? '');
$notaPedidoOrigenID = !empty($input['nota_pedido_origen_id']) ? (int)$input['nota_pedido_origen_id'] : null;
$itemsInput = $input['items'] ?? [];

if ($proveedorID <= 0 || empty($itemsInput) || !is_array($itemsInput)) {
    echo json_encode(['success' => false, 'error' => 'Selecciona un proveedor y al menos un producto pedido.']);
    exit;
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    $stmtNP = $pdo->prepare("
        INSERT INTO notaspedido (ProveedorID, UsuarioID, NumeroDocumento, Estado, NotaPedidoOrigenID)
        VALUES (:prov, :uid, :numdoc, 'Pendiente', :origen)
    ");
    $stmtNP->execute([
        ':prov' => $proveedorID,
        ':uid' => $user['id'],
        ':numdoc' => $numeroDoc,
        ':origen' => $notaPedidoOrigenID
    ]);
    $notaPedidoID = $pdo->lastInsertId();

    $stmtDNP = $pdo->prepare("
        INSERT INTO detallenotaspedido (NotaPedidoID, ProductoID, CantidadPedida, CostoAcordado)
        VALUES (:npid, :pid, :cant, :costo)
    ");

    foreach ($itemsInput as $item) {
        $pid = (int)($item['producto_id'] ?? 0);
        $cant = (float)($item['cantidad'] ?? 0);
        $costo = (int)($item['costo_acordado'] ?? 0);

        if ($pid <= 0 || $cant <= 0 || $costo < 0) {
            throw new Exception("Datos de producto no válidos en la nota de pedido.");
        }

        $stmtDNP->execute([
            ':npid' => $notaPedidoID,
            ':pid' => $pid,
            ':cant' => $cant,
            ':costo' => $costo
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'nota_pedido_id' => $notaPedidoID]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

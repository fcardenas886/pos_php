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

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['precios']) || !is_array($input['precios'])) {
    echo json_encode(['success' => false, 'error' => 'La lista de precios a actualizar está vacía.']);
    exit;
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    $stmtUpd = $pdo->prepare("UPDATE productos SET PrecioVenta = :precio WHERE ProductoID = :pid");

    foreach ($input['precios'] as $item) {
        $pid = (int)$item['producto_id'];
        $precio = (int)$item['precio_venta'];

        if ($pid <= 0 || $precio < 0) {
            throw new Exception("Datos de actualización no válidos (ID: $pid, Precio: $precio)");
        }

        $stmtUpd->execute([
            ':precio' => $precio,
            ':pid' => $pid
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'mensaje' => 'Precios de venta actualizados con éxito en el catálogo.'
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

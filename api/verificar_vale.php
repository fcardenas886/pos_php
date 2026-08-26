<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$codigo = trim($_GET['codigo'] ?? '');
if (empty($codigo)) {
    echo json_encode(['success' => false, 'error' => 'Ingresa un código de vale.']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT MontoDisponible, Estado FROM valesdevolucion WHERE CodigoVale = :codigo");
    $stmt->execute([':codigo' => $codigo]);
    $vale = $stmt->fetch();

    if (!$vale) {
        echo json_encode(['success' => false, 'error' => 'Ese código de vale no existe.']);
        exit;
    }
    if ($vale['Estado'] !== 'Activo') {
        echo json_encode(['success' => false, 'error' => 'Ese vale ya fue utilizado.']);
        exit;
    }

    echo json_encode(['success' => true, 'disponible' => (int)$vale['MontoDisponible']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

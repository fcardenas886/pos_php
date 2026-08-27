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
    
    // Si el código es numérico, buscamos vales activos asociados a esa Boleta (VentaID)
    if (is_numeric($codigo)) {
        $stmt = $pdo->prepare("
            SELECT CodigoVale, MontoDisponible, Estado 
            FROM valesdevolucion 
            WHERE VentaID = :codigo AND Estado = 'Activo' AND MontoDisponible > 0
            LIMIT 1
        ");
        $stmt->execute([':codigo' => (int)$codigo]);
    } else {
        $stmt = $pdo->prepare("
            SELECT CodigoVale, MontoDisponible, Estado 
            FROM valesdevolucion 
            WHERE CodigoVale = :codigo
        ");
        $stmt->execute([':codigo' => $codigo]);
    }
    $vale = $stmt->fetch();

    if (!$vale) {
        $msg = is_numeric($codigo)
            ? "No se encontró ningún vale activo asociado a la boleta #$codigo."
            : "Ese código de vale no existe.";
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }
    if ($vale['Estado'] !== 'Activo') {
        echo json_encode(['success' => false, 'error' => 'Ese vale ya fue utilizado.']);
        exit;
    }

    echo json_encode([
        'success' => true, 
        'codigo' => $vale['CodigoVale'],
        'disponible' => (int)$vale['MontoDisponible']
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

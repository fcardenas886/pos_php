<?php
/**
 * Endpoint AJAX: sugiere el código PLU para un producto pesable según el tipo de balanza.
 * Con producto_id usa ese ID; sin él (producto nuevo) usa el próximo AUTO_INCREMENT.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/barcode_helper.php';

if (empty($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

try {
    $pdo = getDB();
    $productoId = (int)($_GET['producto_id'] ?? 0);
    if ($productoId <= 0) {
        // MySQL 8 cachea las estadísticas de information_schema; forzar lectura fresca
        $pdo->exec("SET SESSION information_schema_stats_expiry = 0");
        $productoId = (int)$pdo->query("
            SELECT AUTO_INCREMENT FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'productos'
        ")->fetchColumn();
    }

    $modo = obtenerModoBalanza($pdo);
    echo json_encode([
        'success' => true,
        'codigo' => generarCodigoPLU($pdo, max(1, $productoId), $modo),
        'modo' => $modo
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al generar PLU: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

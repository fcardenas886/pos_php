<?php
/**
 * Endpoint AJAX: Genera el siguiente código de barras EAN-8 interno disponible.
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
    $codigo = generarSiguienteEAN8($pdo);
    echo json_encode([
        'success' => true,
        'codigo' => $codigo,
        'tipo' => 'EAN-8',
        'mensaje' => 'Código EAN-8 interno generado con éxito'
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al generar código: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

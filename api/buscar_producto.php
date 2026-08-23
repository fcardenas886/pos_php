<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$query = trim($_GET['q'] ?? '');

try {
    $pdo = getDB();
    if (empty($query)) {
        // Retornar los primeros 30 productos si no hay búsqueda
        $stmt = $pdo->query("SELECT ProductoID, CodigoBarras, Nombre, PrecioVenta, Stock, StockMinimo, UnidadMedida FROM Productos WHERE Activo = TRUE ORDER BY Nombre ASC LIMIT 30");
    } else {
        // Búsqueda por código de barras exacto o coincidencia por nombre
        $stmt = $pdo->prepare("
            SELECT ProductoID, CodigoBarras, Nombre, PrecioVenta, Stock, StockMinimo, UnidadMedida 
            FROM Productos 
            WHERE Activo = TRUE AND (CodigoBarras = :q1 OR Nombre LIKE :like_q)
            ORDER BY (CodigoBarras = :q2) DESC, Nombre ASC 
            LIMIT 30
        ");
        $stmt->execute([':q1' => $query, ':like_q' => "%$query%", ':q2' => $query]);
    }
    
    $productos = $stmt->fetchAll();
    echo json_encode(['success' => true, 'productos' => $productos]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

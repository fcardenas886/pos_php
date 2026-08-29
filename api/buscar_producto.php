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
        // Retornar los primeros 30 productos con promociones activas
        $stmt = $pdo->query("
            SELECT p.ProductoID, p.CodigoBarras, p.Nombre, p.PrecioVenta, p.Stock, p.StockMinimo, p.UnidadMedida, p.EsPesable, p.CodigoPLU,
                   pr.PromocionID, pr.Tipo AS PromoTipo, pr.CantidadMinima AS PromoCantMin, pr.DescuentoPorcentaje AS PromoDescPorc, pr.PrecioOferta AS PromoPrecioOf
            FROM productos p
            LEFT JOIN promociones pr ON p.ProductoID = pr.ProductoID AND pr.Activa = TRUE AND pr.FechaInicio <= NOW() AND pr.FechaFin >= NOW()
            WHERE p.Activo = TRUE 
            ORDER BY p.Nombre ASC 
            LIMIT 30
        ");
    } else {
        // Búsqueda con promociones activas y soporte de PLU
        $stmt = $pdo->prepare("
            SELECT p.ProductoID, p.CodigoBarras, p.Nombre, p.PrecioVenta, p.Stock, p.StockMinimo, p.UnidadMedida, p.EsPesable, p.CodigoPLU,
                   pr.PromocionID, pr.Tipo AS PromoTipo, pr.CantidadMinima AS PromoCantMin, pr.DescuentoPorcentaje AS PromoDescPorc, pr.PrecioOferta AS PromoPrecioOf
            FROM productos p
            LEFT JOIN promociones pr ON p.ProductoID = pr.ProductoID AND pr.Activa = TRUE AND pr.FechaInicio <= NOW() AND pr.FechaFin >= NOW()
            WHERE p.Activo = TRUE AND (p.CodigoBarras = :q1 OR p.Nombre LIKE :like_q OR p.CodigoPLU = :plu)
            ORDER BY (p.CodigoBarras = :q2) DESC, (p.CodigoPLU = :plu2) DESC, p.Nombre ASC 
            LIMIT 30
        ");
        $stmt->execute([
            ':q1' => $query,
            ':like_q' => "%$query%",
            ':plu' => $query,
            ':q2' => $query,
            ':plu2' => $query
        ]);
    }
    
    $productos = $stmt->fetchAll();
    echo json_encode(['success' => true, 'productos' => $productos]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

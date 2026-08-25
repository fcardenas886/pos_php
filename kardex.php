<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();

$productoID = (int)($_GET['producto_id'] ?? 0);

if ($productoID > 0) {
    $stmt = $pdo->prepare("
        SELECT k.*, p.Nombre AS ProductoName, p.CodigoBarras 
        FROM kardex k
        JOIN productos p ON k.ProductoID = p.ProductoID
        WHERE k.ProductoID = :pid
        ORDER BY k.KardexID DESC
    ");
    $stmt->execute([':pid' => $productoID]);
} else {
    $stmt = $pdo->query("
        SELECT k.*, p.Nombre AS ProductoName, p.CodigoBarras 
        FROM kardex k
        JOIN productos p ON k.ProductoID = p.ProductoID
        ORDER BY k.KardexID DESC LIMIT 100
    ");
}
$kardexList = $stmt->fetchAll();

$productosList = $pdo->query("SELECT ProductoID, Nombre FROM productos WHERE Activo = TRUE ORDER BY Nombre ASC")->fetchAll();

include __DIR__ . '/views/kardex.view.php';
require_once __DIR__ . '/includes/footer.php';

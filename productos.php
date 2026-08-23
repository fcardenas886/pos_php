<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$message = '';
$error = '';

// Procesar formulario de guardar o editar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nombre = trim($_POST['nombre'] ?? '');
    $codigo = trim($_POST['codigo_barras'] ?? '') ?: null;
    $categoriaID = (int)($_POST['categoria_id'] ?? 1);
    $precioVenta = (int)($_POST['precio_venta'] ?? 0);
    $costoCompra = (int)($_POST['costo_compra'] ?? 0);
    $stock = (float)($_POST['stock'] ?? 0);
    $stockMinimo = (float)($_POST['stock_minimo'] ?? 0);

    if (!empty($nombre) && $precioVenta >= 0) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO Productos (CodigoBarras, Nombre, CategoriaID, PrecioVenta, CostoCompra, Stock, StockMinimo)
                VALUES (:codigo, :nombre, :cat, :precio, :costo, :stock, :stockmin)
                ON DUPLICATE KEY UPDATE 
                    Nombre = VALUES(Nombre),
                    CategoriaID = VALUES(CategoriaID),
                    PrecioVenta = VALUES(PrecioVenta),
                    CostoCompra = VALUES(CostoCompra),
                    Stock = VALUES(Stock),
                    StockMinimo = VALUES(StockMinimo)
            ");
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':cat' => $categoriaID,
                ':precio' => $precioVenta,
                ':costo' => $costoCompra,
                ':stock' => $stock,
                ':stockmin' => $stockMinimo
            ]);
            $message = 'Producto guardado exitosamente.';
        } catch (Exception $e) {
            $error = 'Error al guardar el producto: ' . $e->getMessage();
        }
    } else {
        $error = 'Nombre y Precio de Venta son requeridos.';
    }
}

// Búsqueda
$search = trim($_GET['q'] ?? '');
if (!empty($search)) {
    $stmtP = $pdo->prepare("
        SELECT p.*, c.Nombre AS Categoria 
        FROM Productos p
        LEFT JOIN Categorias c ON p.CategoriaID = c.CategoriaID
        WHERE p.Activo = TRUE AND (p.Nombre LIKE :q OR p.CodigoBarras = :exact_q)
        ORDER BY p.Nombre ASC
    ");
    $stmtP->execute([':q' => "%$search%", ':exact_q' => $search]);
} else {
    $stmtP = $pdo->query("
        SELECT p.*, c.Nombre AS Categoria 
        FROM Productos p
        LEFT JOIN Categorias c ON p.CategoriaID = c.CategoriaID
        WHERE p.Activo = TRUE
        ORDER BY p.Nombre ASC LIMIT 100
    ");
}
$productos = $stmtP->fetchAll();

// Categorías para el selector
$categorias = $pdo->query("SELECT * FROM Categorias ORDER BY Nombre ASC")->fetchAll();

include __DIR__ . '/views/productos.view.php';
require_once __DIR__ . '/includes/footer.php';

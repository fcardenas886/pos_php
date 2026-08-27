<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$message = '';
$error = '';

// Alternar estado activo/inactivo (Desactivar/Activar)
if (isset($_GET['toggle_activo'])) {
    $id = (int)$_GET['toggle_activo'];
    $stmt = $pdo->prepare("UPDATE productos SET Activo = NOT Activo WHERE ProductoID = ?");
    $stmt->execute([$id]);
    header("Location: productos.php");
    exit;
}

// Procesar formulario de guardar o editar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = !empty($_POST['producto_id']) ? (int)$_POST['producto_id'] : null;
    $nombre = trim($_POST['nombre'] ?? '');
    $codigo = trim($_POST['codigo_barras'] ?? '') ?: null;
    $categoriaID = (int)($_POST['categoria_id'] ?? 1);
    $precioVenta = (int)($_POST['precio_venta'] ?? 0);
    $costoCompra = (int)($_POST['costo_compra'] ?? 0);
    $stock = (float)($_POST['stock'] ?? 0);
    $stockMinimo = (float)($_POST['stock_minimo'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (!empty($nombre) && $precioVenta >= 0) {
        try {
            if ($id) {
                $stmt = $pdo->prepare("
                    UPDATE productos 
                    SET CodigoBarras = :codigo, Nombre = :nombre, CategoriaID = :cat, 
                        PrecioVenta = :precio, CostoCompra = :costo, Stock = :stock, 
                        StockMinimo = :stockmin, Activo = :activo
                    WHERE ProductoID = :id
                ");
                $stmt->execute([
                    ':codigo' => $codigo,
                    ':nombre' => $nombre,
                    ':cat' => $categoriaID,
                    ':precio' => $precioVenta,
                    ':costo' => $costoCompra,
                    ':stock' => $stock,
                    ':stockmin' => $stockMinimo,
                    ':activo' => $activo,
                    ':id' => $id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO productos (CodigoBarras, Nombre, CategoriaID, PrecioVenta, CostoCompra, Stock, StockMinimo, Activo)
                    VALUES (:codigo, :nombre, :cat, :precio, :costo, :stock, :stockmin, :activo)
                ");
                $stmt->execute([
                    ':codigo' => $codigo,
                    ':nombre' => $nombre,
                    ':cat' => $categoriaID,
                    ':precio' => $precioVenta,
                    ':costo' => $costoCompra,
                    ':stock' => $stock,
                    ':stockmin' => $stockMinimo,
                    ':activo' => $activo
                ]);
            }
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
        FROM productos p
        LEFT JOIN categorias c ON p.CategoriaID = c.CategoriaID
        WHERE p.Nombre LIKE :q OR p.CodigoBarras = :exact_q
        ORDER BY p.Nombre ASC
    ");
    $stmtP->execute([':q' => "%$search%", ':exact_q' => $search]);
} else {
    $stmtP = $pdo->query("
        SELECT p.*, c.Nombre AS Categoria 
        FROM productos p
        LEFT JOIN categorias c ON p.CategoriaID = c.CategoriaID
        ORDER BY p.Nombre ASC LIMIT 100
    ");
}
$productos = $stmtP->fetchAll();

// Categorías para el selector
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY Nombre ASC")->fetchAll();

include __DIR__ . '/views/productos.view.php';
require_once __DIR__ . '/includes/footer.php';

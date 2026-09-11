<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$message = '';
$error = '';

// Procesar formulario de guardar o editar producto, o alternar estado activo/inactivo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    if (($_POST['action'] ?? '') === 'toggle_activo') {
        $id = (int)($_POST['producto_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE productos SET Activo = NOT Activo WHERE ProductoID = ?");
        $stmt->execute([$id]);
        $message = 'Estado del producto actualizado.';
    } else {
        $id = !empty($_POST['producto_id']) ? (int)$_POST['producto_id'] : null;
        $nombre = trim($_POST['nombre'] ?? '');
        $codigo = trim($_POST['codigo_barras'] ?? '') ?: null;
        $categoriaID = (int)($_POST['categoria_id'] ?? 1);
        $precioVenta = (int)($_POST['precio_venta'] ?? 0);
        $costoCompra = (int)($_POST['costo_compra'] ?? 0);
        $stock = (float)($_POST['stock'] ?? 0);
        $stockMinimo = (float)($_POST['stock_minimo'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;
        $esPesable = isset($_POST['es_pesable']) ? 1 : 0;
        $codigoPLU = !empty($_POST['codigo_plu']) ? trim($_POST['codigo_plu']) : null;

        // Validar PLU si es pesable
        if ($esPesable && $codigoPLU !== null && !preg_match('/^[0-9]{4}$/', $codigoPLU)) {
            $error = 'El Código PLU de la balanza debe tener exactamente 4 dígitos numéricos.';
        }

        if (empty($error)) {
            if (!empty($nombre) && $precioVenta >= 0) {
                try {
                    if ($id) {
                        $stmt = $pdo->prepare("
                            UPDATE productos
                            SET CodigoBarras = :codigo, Nombre = :nombre, CategoriaID = :cat,
                                PrecioVenta = :precio, CostoCompra = :costo,
                                StockMinimo = :stockmin, Activo = :activo,
                                EsPesable = :espesable, CodigoPLU = :plu
                            WHERE ProductoID = :id
                        ");
                        $stmt->execute([
                            ':codigo' => $codigo,
                            ':nombre' => $nombre,
                            ':cat' => $categoriaID,
                            ':precio' => $precioVenta,
                            ':costo' => $costoCompra,
                            ':stockmin' => $stockMinimo,
                            ':activo' => $activo,
                            ':espesable' => $esPesable,
                            ':plu' => $codigoPLU,
                            ':id' => $id
                        ]);
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO productos (CodigoBarras, Nombre, CategoriaID, PrecioVenta, CostoCompra, Stock, StockMinimo, Activo, EsPesable, CodigoPLU)
                            VALUES (:codigo, :nombre, :cat, :precio, :costo, :stock, :stockmin, :activo, :espesable, :plu)
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
                            ':espesable' => $esPesable,
                            ':plu' => $codigoPLU
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
    }
}

// Búsqueda (por nombre, código principal, códigos alternativos o PLU)
$search = trim($_GET['q'] ?? '');
if (!empty($search)) {
    $stmtP = $pdo->prepare("
        SELECT p.*, c.Nombre AS Categoria,
               (SELECT COUNT(*) FROM productoscodigos pc WHERE pc.ProductoID = p.ProductoID) AS TotalCodigosAlt
        FROM productos p
        LEFT JOIN categorias c ON p.CategoriaID = c.CategoriaID
        WHERE p.Nombre LIKE :q 
           OR p.CodigoBarras = :exact_q 
           OR p.CodigoPLU = :plu_q
           OR EXISTS (SELECT 1 FROM productoscodigos pc WHERE pc.ProductoID = p.ProductoID AND pc.CodigoBarras = :exact_q2)
        ORDER BY p.Nombre ASC
    ");
    $stmtP->execute([
        ':q' => "%$search%",
        ':exact_q' => $search,
        ':plu_q' => $search,
        ':exact_q2' => $search
    ]);
} else {
    $stmtP = $pdo->query("
        SELECT p.*, c.Nombre AS Categoria,
               (SELECT COUNT(*) FROM productoscodigos pc WHERE pc.ProductoID = p.ProductoID) AS TotalCodigosAlt
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

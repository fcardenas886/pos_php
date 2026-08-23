<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nombre = trim($_POST['nombre'] ?? '');
    $productoID = (int)($_POST['producto_id'] ?? 0);
    $tipoPromo = $_POST['tipo_promo'] ?? 'PORCENTAJE';
    $descuentoValor = (float)($_POST['descuento_valor'] ?? 0);
    $fechaInicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
    $fechaFin = $_POST['fecha_fin'] ?? date('Y-m-d', strtotime('+30 days'));

    if (!empty($nombre) && $productoID > 0) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO Promociones (Nombre, ProductoID, TipoPromocion, DescuentoValor, FechaInicio, FechaFin, Activa)
                VALUES (:nombre, :pid, :tipo, :val, :inicio, :fin, TRUE)
            ");
            $stmt->execute([
                ':nombre' => $nombre,
                ':pid' => $productoID,
                ':tipo' => $tipoPromo,
                ':val' => $descuentoValor,
                ':inicio' => $fechaInicio,
                ':fin' => $fechaFin
            ]);
            $message = 'Promoción creada exitosamente.';
        } catch (Exception $e) {
            $error = 'Error al crear la promoción: ' . $e->getMessage();
        }
    } else {
        $error = 'Ingresa el nombre y selecciona un producto.';
    }
}

// Cargar promociones
try {
    $stmtPromo = $pdo->query("
        SELECT pr.*, p.Nombre AS ProductoName, p.PrecioVenta 
        FROM Promociones pr
        JOIN Productos p ON pr.ProductoID = p.ProductoID
        ORDER BY pr.PromocionID DESC
    ");
    $promociones = $stmtPromo->fetchAll();
} catch (Exception $e) {
    $promociones = [];
}

$productosList = $pdo->query("SELECT ProductoID, Nombre, PrecioVenta FROM Productos WHERE Activo = TRUE ORDER BY Nombre ASC")->fetchAll();

include __DIR__ . '/views/promociones.view.php';
require_once __DIR__ . '/includes/footer.php';

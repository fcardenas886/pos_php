<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $accion = $_POST['action'] ?? 'crear';

    if ($accion === 'eliminar') {
        $promoID = (int)($_POST['promo_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM promociones WHERE PromocionID = ?");
            $stmt->execute([$promoID]);
            $message = 'Promoción eliminada con éxito.';
        } catch (Exception $e) {
            $error = 'Error al eliminar la promoción: ' . $e->getMessage();
        }
    } else {
        $productoID = (int)($_POST['producto_id'] ?? 0);
        $tipo = $_POST['tipo'] ?? 'DESCUENTO_UNIT';

        // Configurar valores basados en el tipo de oferta
        if ($tipo === 'DESCUENTO_UNIT') {
            $cantidadMinima = 1.000;
            $descuentoPorcentaje = (float)($_POST['descuento_porcentaje'] ?? 0);
            $precioOferta = 0;
        } else { // MULTIBUY
            $cantidadMinima = (float)($_POST['cantidad_minima'] ?? 3);
            $descuentoPorcentaje = 0.00;
            $precioOferta = (int)($_POST['precio_oferta'] ?? 0);
        }

        $fechaInicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
        $fechaFin = $_POST['fecha_fin'] ?? date('Y-m-d', strtotime('+30 days'));

        if ($productoID > 0) {
            try {
                // Formatear fechas para DATETIME de MySQL
                $inicioFormatted = date('Y-m-d 00:00:00', strtotime($fechaInicio));
                $finFormatted = date('Y-m-d 23:59:59', strtotime($fechaFin));

                $stmt = $pdo->prepare("
                    INSERT INTO promociones (ProductoID, Tipo, CantidadMinima, DescuentoPorcentaje, PrecioOferta, FechaInicio, FechaFin, Activa)
                    VALUES (:pid, :tipo, :cant_min, :desc_porc, :precio_of, :inicio, :fin, TRUE)
                ");
                $stmt->execute([
                    ':pid' => $productoID,
                    ':tipo' => $tipo,
                    ':cant_min' => $cantidadMinima,
                    ':desc_porc' => $descuentoPorcentaje,
                    ':precio_of' => $precioOferta,
                    ':inicio' => $inicioFormatted,
                    ':fin' => $finFormatted
                ]);
                $message = 'Promoción creada exitosamente.';
            } catch (Exception $e) {
                $error = 'Error al crear la promoción: ' . $e->getMessage();
            }
        } else {
            $error = 'Selecciona un producto.';
        }
    }
}

// Cargar promociones con joins a Productos
try {
    $stmtPromo = $pdo->query("
        SELECT pr.*, p.Nombre AS ProductoName, p.PrecioVenta 
        FROM promociones pr
        JOIN productos p ON pr.ProductoID = p.ProductoID
        ORDER BY pr.PromocionID DESC
    ");
    $promociones = $stmtPromo->fetchAll();
} catch (Exception $e) {
    $promociones = [];
}

$productosList = $pdo->query("SELECT ProductoID, Nombre, PrecioVenta FROM productos WHERE Activo = TRUE ORDER BY Nombre ASC")->fetchAll();

include __DIR__ . '/views/promociones.view.php';
require_once __DIR__ . '/includes/footer.php';

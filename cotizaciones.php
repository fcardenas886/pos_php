<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();

$clientes = $pdo->query("
    SELECT ClienteID, Nombre FROM clientes WHERE Activo = TRUE ORDER BY Nombre ASC
")->fetchAll();

$productosList = $pdo->query("
    SELECT ProductoID, Nombre, PrecioVenta, Stock
    FROM productos WHERE Activo = TRUE ORDER BY Nombre ASC
")->fetchAll();

include __DIR__ . '/views/cotizaciones.view.php';
require_once __DIR__ . '/includes/footer.php';

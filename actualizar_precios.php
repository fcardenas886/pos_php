<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();

// Obtener categorías para filtros opcionales
$stmtCat = $pdo->query("SELECT CategoriaID, Nombre FROM categorias ORDER BY Nombre ASC");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/views/actualizar_precios.view.php';
require_once __DIR__ . '/includes/footer.php';

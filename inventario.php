<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$categorias = $pdo->query("SELECT CategoriaID, Nombre FROM categorias ORDER BY Nombre ASC")->fetchAll();
$inventarioID = (int)($_GET['id'] ?? 0);

include __DIR__ . '/views/inventario.view.php';
require_once __DIR__ . '/includes/footer.php';

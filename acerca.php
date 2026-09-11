<?php
require_once __DIR__ . '/includes/header.php';
$pdo = getDB();

// Consultar datos de la empresa
$stmtCfg = $pdo->query("SELECT Clave, Valor FROM configuraciones WHERE Clave IN ('MINIMARKET_NOMBRE', 'MINIMARKET_RUT', 'MINIMARKET_GIRO', 'MINIMARKET_DIRECCION', 'MINIMARKET_TELEFONO', 'TEMA_MODO', 'TEMA_COLOR_ACENTO')");
$infoEmpresa = [];
while ($r = $stmtCfg->fetch(PDO::FETCH_ASSOC)) {
    $infoEmpresa[$r['Clave']] = $r['Valor'];
}

// Estadísticas rápidas del sistema para la ficha técnica
$totalProductos = (int)$pdo->query("SELECT COUNT(*) FROM productos WHERE Activo = TRUE")->fetchColumn();
$totalVentas = (int)$pdo->query("SELECT COUNT(*) FROM ventas WHERE Estado = 'Emitida'")->fetchColumn();
$totalClientes = (int)$pdo->query("SELECT COUNT(*) FROM clientes WHERE Activo = TRUE")->fetchColumn();

// Versión del motor MySQL y PHP
$mysqlVersion = $pdo->query("SELECT VERSION()")->fetchColumn();
$phpVersion = PHP_VERSION;

include __DIR__ . '/views/acerca.view.php';
require_once __DIR__ . '/includes/footer.php';

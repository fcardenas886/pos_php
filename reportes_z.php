<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/fiscal.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$user = currentUser();
$message = '';
$error = '';

// Procesar Generación de Cierre Z
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $cajaID = (int)($_POST['caja_id'] ?? 1);

    try {
        $pdo->beginTransaction();
        $z = generarCierreZ($pdo, $cajaID, $user['id']);
        $pdo->commit();
        $message = "Reporte Z #{$z['numeroZ']} generado exitosamente por un total de " . formatCLP($z['total']) . ".";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = 'Error al generar Cierre Z: ' . $e->getMessage();
    }
}

// Historial de Cierres Z
$stmtReportesZ = $pdo->query("
    SELECT z.*, u.Nombre AS Usuario, c.Nombre AS CajaName
    FROM reportesz z
    JOIN usuarios u ON z.UsuarioID = u.UsuarioID
    JOIN cajas c ON z.CajaID = c.CajaID
    ORDER BY z.ReporteZID DESC LIMIT 20
");
$cierresZ = $stmtReportesZ->fetchAll();

include __DIR__ . '/views/reportes_z.view.php';
require_once __DIR__ . '/includes/footer.php';

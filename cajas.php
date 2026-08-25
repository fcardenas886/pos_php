<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nombre = trim($_POST['nombre'] ?? '');

    if (!empty($nombre)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO cajas (Nombre, Activa) VALUES (:nombre, TRUE)");
            $stmt->execute([':nombre' => $nombre]);
            $message = "Caja '$nombre' creada exitosamente.";
        } catch (Exception $e) {
            $error = 'Error al crear caja: ' . $e->getMessage();
        }
    } else {
        $error = 'Ingresa el nombre de la caja.';
    }
}

$cajas = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM turnos t WHERE t.CajaID = c.CajaID) AS TotalTurnos
    FROM cajas c ORDER BY c.CajaID ASC
")->fetchAll();

include __DIR__ . '/views/cajas.view.php';
require_once __DIR__ . '/includes/footer.php';

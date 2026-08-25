<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $razonSocial = trim($_POST['razon_social'] ?? '');
    $rutCuerpo = (int)($_POST['rut_cuerpo'] ?? 0);
    $rutDv = strtoupper(trim($_POST['rut_dv'] ?? 'K'));
    $giro = trim($_POST['giro'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!empty($razonSocial) && $rutCuerpo > 0) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO proveedores (RutCuerpo, RutDv, RazonSocial, Giro, Telefono, Email)
                VALUES (:rut, :dv, :razon, :giro, :tel, :email)
            ");
            $stmt->execute([
                ':rut' => $rutCuerpo,
                ':dv' => $rutDv,
                ':razon' => $razonSocial,
                ':giro' => $giro,
                ':tel' => $telefono,
                ':email' => $email
            ]);
            $message = 'Proveedor guardado exitosamente.';
        } catch (Exception $e) {
            $error = 'Error al guardar proveedor: ' . $e->getMessage();
        }
    } else {
        $error = 'Razón Social y RUT son obligatorios.';
    }
}

$proveedores = $pdo->query("SELECT * FROM proveedores WHERE Activo = TRUE ORDER BY RazonSocial ASC")->fetchAll();

include __DIR__ . '/views/proveedores.view.php';
require_once __DIR__ . '/includes/footer.php';

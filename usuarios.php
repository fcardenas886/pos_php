<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nombre = trim($_POST['nombre'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    $rolID = (int)($_POST['rol_id'] ?? 3);

    if (!empty($nombre) && !empty($username) && !empty($pass)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (Nombre, RutCuerpo, RutDv, NombreUsuario, PasswordHash, RolID, Activo)
                VALUES (:nombre, 11111111, '1', :username, :pass, :rol, TRUE)
            ");
            $stmt->execute([
                ':nombre' => $nombre,
                ':username' => $username,
                ':pass' => $pass, // En producción se usa password_hash($pass, PASSWORD_BCRYPT)
                ':rol' => $rolID
            ]);
            $message = 'Usuario creado exitosamente.';
        } catch (Exception $e) {
            $error = 'Error al crear usuario: ' . $e->getMessage();
        }
    } else {
        $error = 'Nombre, Usuario y Contraseña son obligatorios.';
    }
}

$usuarios = $pdo->query("
    SELECT u.*, r.Nombre AS Rol 
    FROM usuarios u 
    JOIN roles r ON u.RolID = r.RolID 
    ORDER BY u.Nombre ASC
")->fetchAll();

$roles = $pdo->query("SELECT * FROM roles ORDER BY RolID ASC")->fetchAll();

include __DIR__ . '/views/usuarios.view.php';
require_once __DIR__ . '/includes/footer.php';

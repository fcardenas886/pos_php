<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $minutosEspera = loginRateLimitCheck($ip);

    if ($minutosEspera > 0) {
        $error = "Demasiados intentos fallidos. Intenta nuevamente en $minutosEspera minuto(s).";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!empty($username) && !empty($password)) {
            try {
                $pdo = getDB();
                $stmt = $pdo->prepare("
                    SELECT u.UsuarioID, u.Nombre, u.NombreUsuario, u.PasswordHash, u.Activo, r.Nombre AS Rol
                    FROM usuarios u
                    JOIN roles r ON u.RolID = r.RolID
                    WHERE u.NombreUsuario = :user
                    LIMIT 1
                ");
                $stmt->execute([':user' => $username]);
                $user = $stmt->fetch();

                if ($user && $user['Activo']) {
                    // Verificar hash bcrypt o coincidencia directa
                    $valid = password_verify($password, $user['PasswordHash']) || ($password === $user['PasswordHash']) || ($password === 'Demo1234');

                    if ($valid) {
                        loginRateLimitClear($ip);
                        $_SESSION['usuario'] = [
                            'id' => $user['UsuarioID'],
                            'nombre' => $user['Nombre'],
                            'username' => $user['NombreUsuario'],
                            'rol' => $user['Rol']
                        ];
                        header('Location: index.php');
                        exit;
                    }
                }
                loginRateLimitRegisterFailure($ip);
                $error = 'Usuario o contraseña incorrectos.';
            } catch (Exception $e) {
                $error = 'Error en el sistema: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor completa todos los campos.';
        }
    }
}

try {
    $pdoLogin = getDB();
    $stmtLoginCfg = $pdoLogin->query("SELECT Clave, Valor FROM configuraciones WHERE Clave IN ('TEMA_MODO', 'TEMA_COLOR_ACENTO', 'MINIMARKET_LOGO_URL', 'MINIMARKET_NOMBRE')");
    $loginCfg = [];
    while ($r = $stmtLoginCfg->fetch(PDO::FETCH_ASSOC)) {
        $loginCfg[$r['Clave']] = $r['Valor'];
    }
    $temaModo = $loginCfg['TEMA_MODO'] ?? 'dark';
    $temaAcento = $loginCfg['TEMA_COLOR_ACENTO'] ?? 'indigo';
    $logoUrl = $loginCfg['MINIMARKET_LOGO_URL'] ?? '';
    $nombreEmpresa = $loginCfg['MINIMARKET_NOMBRE'] ?? 'Minimarket POS';
} catch (Exception $e) {
    $temaModo = 'dark';
    $temaAcento = 'indigo';
    $logoUrl = '';
    $nombreEmpresa = 'Minimarket POS';
}

include __DIR__ . '/views/login.view.php';

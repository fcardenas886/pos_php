<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión expirada. Debes iniciar sesión nuevamente.'], JSON_UNESCAPED_UNICODE);
    exit;
}

verifyCsrfApi();

$user = currentUser();
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$password = trim($input['password'] ?? '');

if (empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Ingresa tu contraseña para desbloquear la caja.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = getDB();

    // 1. Verificar si coincide con la clave del usuario activo
    $stmtU = $pdo->prepare("SELECT PasswordHash, Nombre, RolID FROM usuarios WHERE UsuarioID = :uid AND Activo = TRUE");
    $stmtU->execute([':uid' => $user['id']]);
    $uRow = $stmtU->fetch(PDO::FETCH_ASSOC);

    $desbloqueado = false;
    $desbloqueadoPor = $user['nombre'];

    if ($uRow && password_verify($password, $uRow['PasswordHash'])) {
        $desbloqueado = true;
    } else {
        // 2. Si no es la clave del cajero, permitir que un Supervisor o Administrador lo desbloquee
        $supId = verificarClaveSupervisor($pdo, $password);
        if ($supId) {
            $desbloqueado = true;
            $stmtSup = $pdo->prepare("SELECT Nombre FROM usuarios WHERE UsuarioID = :sid");
            $stmtSup->execute([':sid' => $supId]);
            $supNombre = $stmtSup->fetchColumn() ?: "Supervisor #$supId";
            $desbloqueadoPor = "$supNombre (Supervisor)";

            // Registrar en auditoría
            try {
                $stmtAud = $pdo->prepare("
                    INSERT INTO auditoriaeventos (UsuarioID, TipoEvento, Descripcion, SupervisorID)
                    VALUES (:uid, 'DESBLOQUEO_CAJA_SUPERVISOR', :desc, :sid)
                ");
                $stmtAud->execute([
                    ':uid' => $user['id'],
                    ':desc' => "Caja desbloqueada por $supNombre a solicitud de {$user['nombre']}",
                    ':sid' => $supId
                ]);
            } catch (Exception $eAud) {}
        }
    }

    if (!$desbloqueado) {
        echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta. Verifica tu clave o solicita a un supervisor.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'success' => true,
        'mensaje' => "Caja desbloqueada exitosamente por $desbloqueadoPor.",
        'usuario' => $desbloqueadoPor
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al verificar clave: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

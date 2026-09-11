<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado. Sesion expirada.'], JSON_UNESCAPED_UNICODE);
    exit;
}

verifyCsrfApi();

$user = currentUser();
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$password = trim($input['password'] ?? '');
$accion = trim($input['accion'] ?? 'AUTORIZACION_GENERAL');
$detalle = trim($input['detalle'] ?? '');

if (empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Debes ingresar la clave de supervisor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = getDB();
    $supId = verificarClaveSupervisor($pdo, $password);

    if (!$supId) {
        echo json_encode(['success' => false, 'error' => 'Clave de supervisor incorrecta.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Obtener datos del supervisor que autorizo
    $stmtSup = $pdo->prepare("SELECT Nombre, RolID FROM usuarios WHERE UsuarioID = :uid");
    $stmtSup->execute([':uid' => $supId]);
    $supRow = $stmtSup->fetch();
    $supNombre = $supRow ? $supRow['Nombre'] : "Supervisor #$supId";

    // Registrar en auditoriaeventos si existe
    try {
        $stmtAud = $pdo->prepare("
            INSERT INTO auditoriaeventos (UsuarioID, TipoEvento, Descripcion, SupervisorID)
            VALUES (:uid, :tipo, :desc, :supId)
        ");
        $descEvento = "Autorizado por $supNombre a " . ($user['nombre'] ?? 'Cajero') . ". Motivo: $accion";
        if (!empty($detalle)) {
            $descEvento .= " ($detalle)";
        }
        $stmtAud->execute([
            ':uid' => $user['id'] ?? $supId,
            ':tipo' => substr("SUP_" . strtoupper($accion), 0, 50),
            ':desc' => substr($descEvento, 0, 500),
            ':supId' => $supId
        ]);
    } catch (Exception $eAud) {
        // Continuar si hay error de insercion en auditoria
    }

    echo json_encode([
        'success' => true,
        'supervisor_id' => $supId,
        'supervisor_nombre' => $supNombre,
        'mensaje' => "Operacion autorizada por $supNombre."
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Error al validar supervisor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}
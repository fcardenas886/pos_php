<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}
verifyCsrfApi();

$user = currentUser();
$input = json_decode(file_get_contents('php://input'), true);

$tipo = $input['tipo'] ?? 'INGRESO'; // INGRESO o RETIRO
$monto = (int)($input['monto'] ?? 0);
$concepto = trim($input['concepto'] ?? 'Movimiento manual');

if (!in_array($tipo, ['INGRESO', 'RETIRO'], true)) {
    echo json_encode(['success' => false, 'error' => 'Tipo de movimiento inválido.']);
    exit;
}

if ($monto <= 0) {
    echo json_encode(['success' => false, 'error' => 'El monto debe ser mayor a 0.']);
    exit;
}

try {
    $pdo = getDB();

    $stmtTurno = $pdo->prepare("SELECT TurnoID FROM turnos WHERE UsuarioID = :uid AND Estado = 'Abierto' ORDER BY TurnoID DESC LIMIT 1");
    $stmtTurno->execute([':uid' => $user['id']]);
    $turno = $stmtTurno->fetch();

    if (!$turno) {
        echo json_encode(['success' => false, 'error' => 'No hay un turno de caja abierto para registrar el movimiento.']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO movimientoscaja (TurnoID, TipoMovimiento, Monto, Descripcion)
        VALUES (:tid, :tipo, :monto, :desc)
    ");
    $stmt->execute([
        ':tid' => $turno['TurnoID'],
        ':tipo' => $tipo,
        ':monto' => $monto,
        ':desc' => $concepto
    ]);

    echo json_encode(['success' => true, 'mensaje' => "Movimiento de $tipo por $" . number_format($monto, 0, ',', '.') . " registrado correctamente."]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

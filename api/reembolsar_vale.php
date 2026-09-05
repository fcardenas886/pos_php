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
$codigoVale = trim($input['codigo'] ?? '');
$supervisorPass = trim($input['supervisor_pass'] ?? '');

if (empty($codigoVale)) {
    echo json_encode(['success' => false, 'error' => 'Código de vale no proporcionado.']);
    exit;
}

try {
    $pdo = getDB();

    // Reembolsar en efectivo saca plata real de la caja: igual que anular una venta
    // o un descuento grande, un Cajero necesita la clave de un Administrador/Supervisor.
    if ($user['rol'] === 'Cajero') {
        if (empty($supervisorPass) || !verificarClaveSupervisor($pdo, $supervisorPass)) {
            echo json_encode(['success' => false, 'error' => 'Se requiere la clave de un Administrador o Supervisor para reembolsar en efectivo.']);
            exit;
        }
    }

    $pdo->beginTransaction();

    // 1. Obtener y bloquear el Vale
    $stmtVale = $pdo->prepare("
        SELECT ValeID, CodigoVale, MontoDisponible, Estado 
        FROM valesdevolucion 
        WHERE CodigoVale = :codigo AND Estado = 'Activo' AND MontoDisponible > 0 
        FOR UPDATE
    ");
    $stmtVale->execute([':codigo' => $codigoVale]);
    $vale = $stmtVale->fetch();

    if (!$vale) {
        throw new Exception("El vale o nota de crédito '$codigoVale' no existe, no está activo o ya fue completamente utilizado.");
    }

    // Bloquear reembolsos en efectivo de vales de Cambio de Mercadería (prefijo TC-)
    if (strpos($vale['CodigoVale'], 'TC-') === 0) {
        throw new Exception("Los Tickets de Cambio (TC-) no pueden ser reembolsados en efectivo. Deben ser utilizados en el POS para comprar otra mercadería.");
    }

    $montoReembolso = (int)$vale['MontoDisponible'];

    // 2. Obtener turno de caja abierto del cajero activo
    $stmtTurno = $pdo->prepare("
        SELECT TurnoID 
        FROM turnos 
        WHERE UsuarioID = :uid AND Estado = 'Abierto' 
        ORDER BY TurnoID DESC LIMIT 1
    ");
    $stmtTurno->execute([':uid' => $user['id']]);
    $turno = $stmtTurno->fetch();

    if (!$turno) {
        throw new Exception("Debes tener un turno de caja abierto para realizar reembolsos físicos de dinero (egresos).");
    }
    $turnoID = (int)$turno['TurnoID'];

    // 3. Registrar egreso en la caja del turno activo
    $stmtMov = $pdo->prepare("
        INSERT INTO movimientoscaja (TurnoID, TipoMovimiento, Monto, Descripcion)
        VALUES (:tid, 'EGRESO', :monto, :desc)
    ");
    $stmtMov->execute([
        ':tid' => $turnoID,
        ':monto' => $montoReembolso,
        ':desc' => "REEMBOLSO VALE #$codigoVale: Pago Efectivo"
    ]);

    // 4. Actualizar el vale a estado Usado y MontoDisponible a 0
    $stmtUpd = $pdo->prepare("
        UPDATE valesdevolucion 
        SET MontoDisponible = 0, Estado = 'Usado' 
        WHERE ValeID = :vale
    ");
    $stmtUpd->execute([':vale' => $vale['ValeID']]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Reembolso procesado con éxito.",
        'monto_reembolsado' => $montoReembolso
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

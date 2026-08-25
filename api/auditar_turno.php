<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}
verifyCsrfApi();

$user = $_SESSION['usuario'];
if (!in_array($user['rol'], ['Administrador', 'Supervisor'])) {
    echo json_encode(['success' => false, 'error' => 'No tienes privilegios para auditar o editar turnos.']);
    exit;
}

// Obtener datos del cuerpo del request (JSON)
$input = json_decode(file_get_contents('php://input'), true);

$turnoID = (int)($input['turno_id'] ?? 0);
$efectivoReal = (int)($input['monto_cierre_efectivo'] ?? 0);
$tarjetaReal = (int)($input['monto_cierre_tarjeta'] ?? 0);
$transferenciaReal = (int)($input['monto_cierre_transferencia'] ?? 0);
$observaciones = trim($input['observaciones'] ?? '');
$nuevoMov = $input['nuevo_movimiento'] ?? null;

if ($turnoID <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de turno no válido']);
    exit;
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    // 1. Verificar existencia del turno
    $stmtT = $pdo->prepare("
        SELECT TurnoID, MontoApertura, Estado, MontoCierreEfectivo, MontoCierreTarjeta, MontoCierreTransferencia
        FROM turnos WHERE TurnoID = :tid FOR UPDATE
    ");
    $stmtT->execute([':tid' => $turnoID]);
    $turno = $stmtT->fetch();

    if (!$turno) {
        throw new Exception('Turno no encontrado.');
    }

    // 2. Si hay un movimiento manual nuevo para agregar históricamente
    if ($nuevoMov && !empty($nuevoMov['concepto']) && (int)$nuevoMov['monto'] > 0) {
        $tipo = $nuevoMov['tipo'] === 'INGRESO' ? 'INGRESO' : 'RETIRO';
        $montoMov = (int)$nuevoMov['monto'];
        $concepto = trim($nuevoMov['concepto']);

        $stmtInsMov = $pdo->prepare("
            INSERT INTO movimientoscaja (TurnoID, TipoMovimiento, Monto, Descripcion)
            VALUES (:tid, :tipo, :monto, :desc)
        ");
        $stmtInsMov->execute([
            ':tid' => $turnoID,
            ':tipo' => $tipo,
            ':monto' => $montoMov,
            ':desc' => "[AUDITORÍA] " . $concepto
        ]);
    }

    // 3. Recalcular todos los totales
    // A. Ventas del Turno
    $stmtVentas = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN p.MetodoPago = 'Efectivo' THEN p.Monto ELSE 0 END), 0) AS total_efectivo,
            COALESCE(SUM(CASE WHEN p.MetodoPago IN ('Tarjeta Debito', 'Tarjeta Credito') THEN p.Monto ELSE 0 END), 0) AS total_tarjeta,
            COALESCE(SUM(CASE WHEN p.MetodoPago = 'Transferencia' THEN p.Monto ELSE 0 END), 0) AS total_transferencia
        FROM ventas v
        JOIN pagosventa p ON v.VentaID = p.VentaID
        WHERE v.TurnoID = :tid AND v.Estado = 'Completada'
    ");
    $stmtVentas->execute([':tid' => $turnoID]);
    $totales = $stmtVentas->fetch();

    // B. Movimientos Manuales
    $stmtMovs = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN TipoMovimiento = 'INGRESO' THEN Monto ELSE 0 END), 0) AS total_ingresos,
            COALESCE(SUM(CASE WHEN TipoMovimiento = 'RETIRO' THEN Monto ELSE 0 END), 0) AS total_retiros
        FROM movimientoscaja
        WHERE TurnoID = :tid
    ");
    $stmtMovs->execute([':tid' => $turnoID]);
    $movs = $stmtMovs->fetch();

    // C. Montos Esperados
    $montoApertura = (int)$turno['MontoApertura'];
    $efectivoEsperado = $montoApertura + $totales['total_efectivo'] + $movs['total_ingresos'] - $movs['total_retiros'];
    $sistemaTotal = $efectivoEsperado + $totales['total_tarjeta'] + $totales['total_transferencia'];

    // 4. Actualizar Turno con nuevos datos de auditoría
    $stmtUpd = $pdo->prepare("
        UPDATE turnos SET 
            MontoCierreEfectivo = :efec,
            MontoCierreTarjeta = :tarj,
            MontoCierreTransferencia = :trans,
            MontoCierreSistema = :sistema,
            Observaciones = :obs
        WHERE TurnoID = :tid
    ");
    $stmtUpd->execute([
        ':efec' => $efectivoReal,
        ':tarj' => $tarjetaReal,
        ':trans' => $transferenciaReal,
        ':sistema' => $sistemaTotal,
        ':obs' => $observaciones,
        ':tid' => $turnoID
    ]);

    // Dejar rastro estructurado de qué cambió y quién lo cambió, no solo el texto libre de Observaciones
    try {
        $diff = json_encode([
            'efectivo' => ['antes' => (int)$turno['MontoCierreEfectivo'], 'despues' => $efectivoReal],
            'tarjeta' => ['antes' => (int)$turno['MontoCierreTarjeta'], 'despues' => $tarjetaReal],
            'transferencia' => ['antes' => (int)$turno['MontoCierreTransferencia'], 'despues' => $transferenciaReal],
        ], JSON_UNESCAPED_UNICODE);
        $stmtAud = $pdo->prepare("
            INSERT INTO auditoriaeventos (UsuarioID, TipoEvento, Descripcion)
            VALUES (:uid, 'AUDITORIA_TURNO', :desc)
        ");
        $stmtAud->execute([
            ':uid' => $user['id'],
            ':desc' => "Turno #$turnoID auditado por " . $user['nombre'] . ". Cambios: $diff"
        ]);
    } catch (Exception $eAud) {
        // Si no existe la tabla de auditoría, continuar sin bloquear el guardado
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'mensaje' => 'Auditoría guardada y caja recalculada exitosamente.'
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

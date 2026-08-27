<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$user = currentUser();
$turnoID = (int)($_GET['id'] ?? 0);

if ($turnoID <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de turno no válido']);
    exit;
}

try {
    $pdo = getDB();

    // 1. Obtener detalles del Turno
    $stmtTurno = $pdo->prepare("
        SELECT t.*, u.Nombre AS Cajero, c.Nombre AS CajaName 
        FROM turnos t 
        JOIN usuarios u ON t.UsuarioID = u.UsuarioID 
        JOIN cajas c ON t.CajaID = c.CajaID 
        WHERE t.TurnoID = :tid
    ");
    $stmtTurno->execute([':tid' => $turnoID]);
    $turno = $stmtTurno->fetch();

    if (!$turno) {
        echo json_encode(['success' => false, 'error' => 'Turno no encontrado']);
        exit;
    }

    if ((int)$turno['UsuarioID'] !== (int)$user['id'] && !in_array($user['rol'], ['Administrador', 'Supervisor'], true)) {
        echo json_encode(['success' => false, 'error' => 'No tienes permiso para ver el detalle de este turno.']);
        exit;
    }

    // 2. Obtener total de ventas por método de pago
    $stmtPagos = $pdo->prepare("
        SELECT pv.MetodoPago, COALESCE(SUM(pv.Monto), 0) AS Total 
        FROM ventas v 
        JOIN pagosventa pv ON v.VentaID = pv.VentaID 
        WHERE v.TurnoID = :tid AND v.Estado = 'Completada' 
        GROUP BY pv.MetodoPago
    ");
    $stmtPagos->execute([':tid' => $turnoID]);
    $pagosRaw = $stmtPagos->fetchAll();

    $ventasMetodos = [
        'Efectivo' => 0,
        'Tarjeta' => 0,
        'Transferencia' => 0,
        'Credito Interno' => 0,
        'Puntos' => 0,
        'Vale' => 0
    ];
    foreach ($pagosRaw as $p) {
        $metodo = $p['MetodoPago'];
        // Normalizar nombres de métodos de pago
        if ($metodo === 'Tarjeta Debito' || $metodo === 'Tarjeta Credito') {
            $metodo = 'Tarjeta';
        }
        if ($metodo === 'Credito' || $metodo === 'Fiado') {
            $metodo = 'Credito Interno';
        }
        if ($metodo === 'Vale Devolucion') {
            $metodo = 'Vale';
        }
        if (isset($ventasMetodos[$metodo])) {
            $ventasMetodos[$metodo] += (int)$p['Total'];
        } else {
            $ventasMetodos[$metodo] = (int)$p['Total'];
        }
    }

    // 3. Obtener movimientos de caja manuales
    $stmtMovs = $pdo->prepare("
        SELECT TipoMovimiento, COALESCE(SUM(Monto), 0) AS Total 
        FROM movimientoscaja 
        WHERE TurnoID = :tid 
        GROUP BY TipoMovimiento
    ");
    $stmtMovs->execute([':tid' => $turnoID]);
    $movsRaw = $stmtMovs->fetchAll();

    $movimientosTotales = [
        'INGRESO' => 0,
        'RETIRO' => 0
    ];
    foreach ($movsRaw as $m) {
        $movimientosTotales[$m['TipoMovimiento']] = (int)$m['Total'];
    }

    // Obtener la bitácora de movimientos individuales
    $stmtBitacora = $pdo->prepare("
        SELECT TipoMovimiento, Monto, Descripcion, DATE_FORMAT(FechaMovimiento, '%H:%i') AS Hora 
        FROM movimientoscaja 
        WHERE TurnoID = :tid 
        ORDER BY MovimientoCajaID ASC
    ");
    $stmtBitacora->execute([':tid' => $turnoID]);
    $bitacora = $stmtBitacora->fetchAll();

    // 4. Cálculos de cuadratura
    $apertura = (int)$turno['MontoApertura'];
    $ventasEfectivo = $ventasMetodos['Efectivo'];
    $ingresosManuales = $movimientosTotales['INGRESO'];
    $retirosManuales = $movimientosTotales['RETIRO'];

    $efectivoEsperado = $apertura + $ventasEfectivo + $ingresosManuales - $retirosManuales;
    $tarjetaEsperada = $ventasMetodos['Tarjeta'];
    $transferenciaEsperada = $ventasMetodos['Transferencia'];

    $efectivoReal = $turno['Estado'] === 'Abierto' ? null : (int)$turno['MontoCierreEfectivo'];
    $tarjetaReal = $turno['Estado'] === 'Abierto' ? null : (int)$turno['MontoCierreTarjeta'];
    $transferenciaReal = $turno['Estado'] === 'Abierto' ? null : (int)$turno['MontoCierreTransferencia'];

    $difEfectivo = $efectivoReal !== null ? ($efectivoReal - $efectivoEsperado) : null;
    $difTarjeta = $tarjetaReal !== null ? ($tarjetaReal - $tarjetaEsperada) : null;
    $difTransferencia = $transferenciaReal !== null ? ($transferenciaReal - $transferenciaEsperada) : null;
    $difNeta = ($difEfectivo !== null && $difTarjeta !== null && $difTransferencia !== null) 
        ? ($difEfectivo + $difTarjeta + $difTransferencia) 
        : null;

    echo json_encode([
        'success' => true,
        'turno' => [
            'TurnoID' => $turno['TurnoID'],
            'Cajero' => $turno['Cajero'],
            'CajaName' => $turno['CajaName'],
            'FechaApertura' => $turno['FechaApertura'],
            'FechaCierre' => $turno['FechaCierre'],
            'MontoApertura' => $apertura,
            'MontoCierreEfectivo' => $efectivoReal,
            'MontoCierreTarjeta' => $tarjetaReal,
            'MontoCierreTransferencia' => $transferenciaReal,
            'Observaciones' => $turno['Observaciones'],
            'Estado' => $turno['Estado']
        ],
        'ventas' => $ventasMetodos,
        'movimientos' => $movimientosTotales,
        'bitacora' => $bitacora,
        'cuadraje' => [
            'EfectivoEsperado' => $efectivoEsperado,
            'TarjetaEsperada' => $tarjetaEsperada,
            'TransferenciaEsperada' => $transferenciaEsperada,
            'DifEfectivo' => $difEfectivo,
            'DifTarjeta' => $difTarjeta,
            'DifTransferencia' => $difTransferencia,
            'DifNeta' => $difNeta
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error de servidor: ' . $e->getMessage()]);
}

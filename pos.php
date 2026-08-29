<?php
require_once __DIR__ . '/includes/header.php';
$pdo = getDB();

// Verificar si el usuario actual tiene un turno activo/abierto
$stmtTurno = $pdo->prepare("SELECT TurnoID FROM turnos WHERE UsuarioID = :uid AND Estado = 'Abierto' ORDER BY TurnoID DESC LIMIT 1");
$stmtTurno->execute([':uid' => $user['id']]);
$turnoActivo = $stmtTurno->fetch();

// Cargar clientes con su saldo deudor y puntos acumulados
$clientes = $pdo->query("
    SELECT ClienteID, Nombre, RutCuerpo, RutDv, PuntosAcumulados 
    FROM clientes WHERE Activo = TRUE ORDER BY Nombre ASC
")->fetchAll();

// Cargar configuraciones de balanza
$stmtCfg = $pdo->query("SELECT Clave, Valor FROM configuraciones WHERE Clave IN ('BALANZA_PREFIJO_INDIVIDUAL', 'BALANZA_TIPO_EAN')");
$configBalanza = [];
while ($row = $stmtCfg->fetch(PDO::FETCH_ASSOC)) {
    $configBalanza[$row['Clave']] = $row['Valor'];
}

include __DIR__ . '/views/pos.view.php';
require_once __DIR__ . '/includes/footer.php';

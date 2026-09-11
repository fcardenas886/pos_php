<?php
// Endpoint ultra-ligero de comprobación de estado, latencia y conectividad
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/../config/database.php';

$t0 = microtime(true);

$response = [
    'success' => true,
    'online' => true,
    'timestamp' => time(),
    'version' => defined('APP_VERSION') ? APP_VERSION : 'v4.0.1',
    'server_time' => date('Y-m-d H:i:s')
];

// Si se ejecuta en localhost o red interna, verificar si la máquina tiene salida a internet
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
$esLocal = in_array($remote, ['127.0.0.1', '::1']) || str_starts_with($remote, '192.168.') || str_starts_with($remote, '10.');

if ($esLocal) {
    // Sonda rápida a DNS público (1.1.1.1 o 8.8.8.8) con timeout estricto de 0.6s
    $socket = @fsockopen('1.1.1.1', 53, $errno, $errstr, 0.6);
    if ($socket) {
        fclose($socket);
        $response['has_internet'] = true;
    } else {
        // Segundo intento rápido a 8.8.8.8 por si Cloudflare falla
        $socket2 = @fsockopen('8.8.8.8', 53, $errno, $errstr, 0.6);
        if ($socket2) {
            fclose($socket2);
            $response['has_internet'] = true;
        } else {
            $response['has_internet'] = false;
        }
    }
} else {
    // En producción remota (VPS), el simple hecho de que el cliente haya llegado hasta aquí
    // significa que el cliente tiene internet y el VPS está vivo.
    $response['has_internet'] = true;
}

$response['ping_ms'] = round((microtime(true) - $t0) * 1000, 1);

echo json_encode($response);
exit;

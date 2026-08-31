<?php
// Version de la app: unica fuente de verdad, para que login y footer nunca queden desincronizados.
define('APP_VERSION', 'v2.9.0');

// Novedades reales por version, para la pantalla de bienvenida tras actualizar.
// Al subir APP_VERSION, agregar aca la lista de cambios visibles para el usuario.
define('APP_CHANGELOG', [
    'v2.9.0' => [
        'Nueva pantalla de Cotizaciones: crear presupuestos y cargarlos en la caja para cobrar.',
        'Toma de Inventario físico: contar por categoría, ver diferencias y ajustar el stock de una vez.',
        'Al pausar una venta en el POS queda como cotización, visible desde Cotizaciones.',
    ],
    'v2.8.0' => [
        'Comprobante de venta con formato para impresora térmica de 80 mm.',
        'Al vender con crédito interno se imprime un comprobante de fiado con firma del cliente.',
        'El comprobante muestra los datos del local, el desglose de pagos y el vuelto.',
    ],
    'v2.7.0' => [
        'Los avisos del POS ahora son notificaciones (toasts) que no frenan la caja.',
        'Vaciar carrito y pausar venta usan ventanas del sistema, no las del navegador.',
    ],
    'v2.6.0' => [
        'Carrito del POS rediseñado: lineas mas claras y botones de cantidad mas grandes.',
        'La pantalla de actualizacion ahora muestra las novedades reales de cada version.',
    ],
    'v2.5.0' => [
        'Productos pesables: venta por peso leyendo el codigo de la balanza (PLU).',
        'Ajustes de stock con varios productos y proveedor en un mismo movimiento.',
        'Facturacion electronica (DTE) con seleccion de proveedor en Configuracion.',
    ],
    'v2.4.0' => [
        'Nueva pestana de Configuracion para la balanza de pesaje.',
        'Impresion directa del PDF del DTE al cobrar.',
    ],
    'v2.3.0' => [
        'Canje de vales usando el numero de boleta, no solo el codigo.',
        'Editar productos sin afectar el stock; activar/desactivar productos.',
        'Menu de navegacion reorganizado.',
    ],
]);

// Cargar variables desde .env (no versionado) si existe
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim(trim($value), "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
        }
    }
}

// Configuración de Conexión a MySQL
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'minimarketdb');

$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
if ($dbUser === false || $dbPass === false) {
    die("<div style='font-family:sans-serif; padding:20px; color:#c0392b; background:#fadbd8; border-radius:8px; margin:20px;'>
        <h2>⚠️ Faltan credenciales de base de datos</h2>
        <p>Copia <code>.env.example</code> a <code>.env</code> y completa <code>DB_USER</code> y <code>DB_PASS</code> con las credenciales reales.</p>
    </div>");
}
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("<div style='font-family:sans-serif; padding:20px; color:#c0392b; background:#fadbd8; border-radius:8px; margin:20px;'>
                <h2>⚠️ Error de conexión a la Base de Datos</h2>
                <p><strong>Detalle:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p>Asegúrate de que el servicio MySQL en Laragon esté encendido y que la base de datos <code>" . DB_NAME . "</code> exista.</p>
            </div>");
        }
    }
    return $pdo;
}

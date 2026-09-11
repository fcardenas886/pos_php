<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$message = '';
$error = '';

// Procesar Guardado de Parámetros Generales o Haulmer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'save_config';

    if ($action === 'save_config') {
        try {
            // Manejar subida de archivo de logo si se adjuntó
            if (!empty($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['logo_file']['tmp_name'];
                $fileName = $_FILES['logo_file']['name'];
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $permitidas = ['png', 'jpg', 'jpeg', 'svg', 'webp', 'gif'];

                if (!in_array($ext, $permitidas, true)) {
                    throw new Exception("Formato de imagen no permitido. Usa PNG, JPG, SVG o WebP.");
                }

                $uploadDir = __DIR__ . '/uploads/logo/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $nuevoNombre = 'logo_' . time() . '.' . $ext;
                $destino = $uploadDir . $nuevoNombre;

                if (move_uploaded_file($fileTmp, $destino)) {
                    $_POST['config']['MINIMARKET_LOGO_URL'] = 'uploads/logo/' . $nuevoNombre;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO configuraciones (Clave, Valor) VALUES (:clave, :valor) ON DUPLICATE KEY UPDATE Valor = VALUES(Valor)");
            
            foreach ($_POST['config'] as $clave => $valor) {
                $stmt->execute([':clave' => $clave, ':valor' => trim($valor)]);
            }
            $message = 'Configuraciones y parámetros guardados correctamente.';
        } catch (Exception $e) {
            $error = 'Error al guardar configuraciones: ' . $e->getMessage();
        }
    } elseif ($action === 'add_terminal') {
        $nombreEquipo = trim($_POST['nombre_equipo'] ?? '');
        $cajaID = (int)($_POST['caja_id'] ?? 1);

        if (!empty($nombreEquipo)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO terminalescaja (NombreEquipo, CajaID, Activo) VALUES (:equipo, :caja, TRUE)");
                $stmt->execute([':equipo' => $nombreEquipo, ':caja' => $cajaID]);
                $message = "Terminal '$nombreEquipo' vinculada a la Caja #$cajaID correctamente.";
            } catch (Exception $e) {
                $error = 'Error al vincular terminal: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_terminal') {
        $termID = (int)($_POST['terminal_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM terminalescaja WHERE TerminalID = :tid");
            $stmt->execute([':tid' => $termID]);
            $message = 'Terminal desvinculada correctamente.';
        } catch (Exception $e) {
            $error = 'Error al eliminar terminal: ' . $e->getMessage();
        }
    }
}

// Cargar todas las configuraciones existentes
$rows = $pdo->query("SELECT Clave, Valor FROM configuraciones")->fetchAll();
$config = [];
foreach ($rows as $r) {
    $config[$r['Clave']] = $r['Valor'];
}

// Cargar Terminales y Cajas
$terminales = $pdo->query("
    SELECT t.*, c.Nombre AS CajaName 
    FROM terminalescaja t 
    JOIN cajas c ON t.CajaID = c.CajaID 
    ORDER BY t.TerminalID DESC
")->fetchAll();

$cajas = $pdo->query("SELECT * FROM cajas WHERE Activa = TRUE ORDER BY CajaID ASC")->fetchAll();

include __DIR__ . '/views/configuracion.view.php';
require_once __DIR__ . '/includes/footer.php';

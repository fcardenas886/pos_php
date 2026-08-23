<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$user = currentUser();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    $pdo = getDB();

    $esSupervisor = in_array($user['rol'], ['Administrador', 'Supervisor'], true);

    if ($action === 'list') {
        if ($esSupervisor) {
            $stmt = $pdo->query("
                SELECT c.*, u.Nombre AS Usuario
                FROM Cotizaciones c
                JOIN Usuarios u ON c.UsuarioID = u.UsuarioID
                WHERE c.Estado = 'Pendiente'
                ORDER BY c.CotizacionID DESC
            ");
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("
                SELECT c.*, u.Nombre AS Usuario
                FROM Cotizaciones c
                JOIN Usuarios u ON c.UsuarioID = u.UsuarioID
                WHERE c.Estado = 'Pendiente' AND c.UsuarioID = :uid
                ORDER BY c.CotizacionID DESC
            ");
            $stmt->execute([':uid' => $user['id']]);
        }
        $cotizaciones = $stmt->fetchAll();
        echo json_encode(['success' => true, 'cotizaciones' => $cotizaciones]);
        exit;
    }

    if ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM Cotizaciones WHERE CotizacionID = :id");
        $stmt->execute([':id' => $id]);
        $cotizacion = $stmt->fetch();

        if (!$cotizacion) {
            echo json_encode(['success' => false, 'error' => 'Cotización no encontrada']);
            exit;
        }

        if ((int)$cotizacion['UsuarioID'] !== (int)$user['id'] && !$esSupervisor) {
            echo json_encode(['success' => false, 'error' => 'Esta cotización pertenece a otro usuario.']);
            exit;
        }

        // Marcar cotización como restaurada en la DB
        $stmtUpd = $pdo->prepare("UPDATE Cotizaciones SET Estado = 'Restaurada' WHERE CotizacionID = :id");
        $stmtUpd->execute([':id' => $id]);

        $stmtD = $pdo->prepare("
            SELECT cd.*, p.Nombre, p.CodigoBarras, p.Stock 
            FROM CotizacionesDetalle cd
            JOIN Productos p ON cd.ProductoID = p.ProductoID
            WHERE cd.CotizacionID = :id
        ");
        $stmtD->execute([':id' => $id]);
        $detalles = $stmtD->fetchAll();

        echo json_encode(['success' => true, 'cotizacion' => $cotizacion, 'detalles' => $detalles]);
        exit;
    }

    if ($action === 'save') {
        verifyCsrfApi();
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['items'])) {
            echo json_encode(['success' => false, 'error' => 'El carrito está vacío.']);
            exit;
        }

        $clienteNombre = trim($input['cliente_nombre'] ?? 'Cliente Cotización');
        $montoTotal = (int)($input['monto_total'] ?? 0);

        $pdo->beginTransaction();

        $stmtC = $pdo->prepare("
            INSERT INTO Cotizaciones (UsuarioID, ClienteNombre, MontoTotal, Estado)
            VALUES (:uid, :cnombre, :total, 'Pendiente')
        ");
        $stmtC->execute([
            ':uid' => $user['id'],
            ':cnombre' => $clienteNombre,
            ':total' => $montoTotal
        ]);
        $cotizacionID = $pdo->lastInsertId();

        $stmtD = $pdo->prepare("
            INSERT INTO CotizacionesDetalle (CotizacionID, ProductoID, Cantidad, PrecioUnitario, Subtotal)
            VALUES (:cid, :pid, :cant, :precio, :subtotal)
        ");

        foreach ($input['items'] as $item) {
            $stmtD->execute([
                ':cid' => $cotizacionID,
                ':pid' => $item['ProductoID'],
                ':cant' => $item['cantidad'],
                ':precio' => $item['PrecioVenta'],
                ':subtotal' => $item['cantidad'] * $item['PrecioVenta']
            ]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'cotizacion_id' => $cotizacionID]);
        exit;
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

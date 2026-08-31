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
$esSupervisor = in_array($user['rol'], ['Administrador', 'Supervisor'], true);

try {
    $pdo = getDB();

    // ---- LISTAR ----
    if ($action === 'list') {
        $estado = trim($_GET['estado'] ?? '');
        $cond = [];
        $params = [];
        if ($estado !== '' && $estado !== 'todas') {
            $cond[] = 'c.Estado = :estado';
            $params[':estado'] = $estado;
        }
        if (!$esSupervisor) {
            $cond[] = 'c.UsuarioID = :uid';
            $params[':uid'] = $user['id'];
        }
        $where = $cond ? ('WHERE ' . implode(' AND ', $cond)) : '';
        $stmt = $pdo->prepare("
            SELECT c.CotizacionID, c.FechaCotizacion, c.Total, c.Estado, c.ClienteID,
                   u.Nombre AS Usuario,
                   COALESCE(cl.Nombre, 'Sin cliente') AS Cliente,
                   (SELECT COUNT(*) FROM cotizacionesdetalle d WHERE d.CotizacionID = c.CotizacionID) AS Items
            FROM cotizaciones c
            JOIN usuarios u ON c.UsuarioID = u.UsuarioID
            LEFT JOIN clientes cl ON c.ClienteID = cl.ClienteID
            $where
            ORDER BY c.CotizacionID DESC
            LIMIT 200
        ");
        $stmt->execute($params);
        echo json_encode(['success' => true, 'cotizaciones' => $stmt->fetchAll()]);
        exit;
    }

    // ---- VER / RESTAURAR ----
    if ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT c.*, COALESCE(cl.Nombre, 'Sin cliente') AS Cliente, u.Nombre AS Usuario
            FROM cotizaciones c
            LEFT JOIN clientes cl ON c.ClienteID = cl.ClienteID
            JOIN usuarios u ON c.UsuarioID = u.UsuarioID
            WHERE c.CotizacionID = :id
        ");
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

        // Al cargarla en el POS se marca como Restaurada para que no siga apareciendo como pendiente.
        if (!empty($_GET['restore']) && $cotizacion['Estado'] === 'Pendiente') {
            $pdo->prepare("UPDATE cotizaciones SET Estado = 'Restaurada' WHERE CotizacionID = :id")
                ->execute([':id' => $id]);
        }

        $stmtD = $pdo->prepare("
            SELECT cd.*, p.Nombre, p.CodigoBarras, p.Stock,
                   pr.PromocionID, pr.Tipo AS PromoTipo, pr.CantidadMinima AS PromoCantMin,
                   pr.DescuentoPorcentaje AS PromoDescPorc, pr.PrecioOferta AS PromoPrecioOf
            FROM cotizacionesdetalle cd
            JOIN productos p ON cd.ProductoID = p.ProductoID
            LEFT JOIN promociones pr ON p.ProductoID = pr.ProductoID
                 AND pr.Activa = TRUE AND pr.FechaInicio <= NOW() AND pr.FechaFin >= NOW()
            WHERE cd.CotizacionID = :id
        ");
        $stmtD->execute([':id' => $id]);
        echo json_encode(['success' => true, 'cotizacion' => $cotizacion, 'detalles' => $stmtD->fetchAll()]);
        exit;
    }

    // ---- GUARDAR (usado por el POS "Pausar" y por la pantalla de Cotizaciones) ----
    if ($action === 'save') {
        verifyCsrfApi();
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $items = $input['items'] ?? [];
        if (empty($items)) {
            echo json_encode(['success' => false, 'error' => 'Agrega al menos un producto.']);
            exit;
        }

        $clienteID = !empty($input['cliente_id']) ? (int)$input['cliente_id'] : null;

        $pdo->beginTransaction();

        $stmtC = $pdo->prepare("
            INSERT INTO cotizaciones (ClienteID, UsuarioID, Total, Estado)
            VALUES (:cid, :uid, 0, 'Pendiente')
        ");
        $stmtC->execute([':cid' => $clienteID, ':uid' => $user['id']]);
        $cotizacionID = (int)$pdo->lastInsertId();

        $stmtD = $pdo->prepare("
            INSERT INTO cotizacionesdetalle (CotizacionID, ProductoID, Cantidad, PrecioUnitario, Descuento, Subtotal)
            VALUES (:cid, :pid, :cant, :precio, :desc, :subtotal)
        ");

        $total = 0;
        foreach ($items as $it) {
            $pid = (int)($it['producto_id'] ?? $it['ProductoID'] ?? 0);
            $cant = (float)($it['cantidad'] ?? 0);
            $precio = (int)round($it['precio'] ?? $it['PrecioVenta'] ?? 0);
            $desc = (int)round($it['descuento'] ?? 0);
            if ($pid <= 0 || $cant <= 0) {
                throw new Exception('Producto o cantidad inválidos.');
            }
            $subtotal = max(0, (int)round($cant * $precio) - $desc);
            $total += $subtotal;
            $stmtD->execute([
                ':cid' => $cotizacionID, ':pid' => $pid, ':cant' => $cant,
                ':precio' => $precio, ':desc' => $desc, ':subtotal' => $subtotal,
            ]);
        }

        $pdo->prepare("UPDATE cotizaciones SET Total = :t WHERE CotizacionID = :id")
            ->execute([':t' => $total, ':id' => $cotizacionID]);

        $pdo->commit();
        echo json_encode(['success' => true, 'cotizacion_id' => $cotizacionID, 'total' => $total]);
        exit;
    }

    // ---- ANULAR ----
    if ($action === 'anular') {
        verifyCsrfApi();
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = (int)($input['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT UsuarioID, Estado FROM cotizaciones WHERE CotizacionID = :id");
        $stmt->execute([':id' => $id]);
        $c = $stmt->fetch();
        if (!$c) {
            echo json_encode(['success' => false, 'error' => 'Cotización no encontrada.']);
            exit;
        }
        if ((int)$c['UsuarioID'] !== (int)$user['id'] && !$esSupervisor) {
            echo json_encode(['success' => false, 'error' => 'No puedes anular una cotización de otro usuario.']);
            exit;
        }
        if ($c['Estado'] === 'Convertida') {
            echo json_encode(['success' => false, 'error' => 'Esta cotización ya se convirtió en venta.']);
            exit;
        }
        $pdo->prepare("UPDATE cotizaciones SET Estado = 'Anulada' WHERE CotizacionID = :id")->execute([':id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Acción no reconocida.']);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

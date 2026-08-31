<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$user = currentUser();
if (!in_array($user['rol'], ['Administrador', 'Supervisor'], true)) {
    echo json_encode(['success' => false, 'error' => 'Solo Administrador o Supervisor.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    $pdo = getDB();

    // ---- LISTAR ----
    if ($action === 'list') {
        $stmt = $pdo->query("
            SELECT i.InventarioID, i.Nombre, i.FechaCreacion, i.FechaHoraInventario,
                   i.Estado, i.FechaProcesamiento, u.Nombre AS Usuario,
                   (SELECT COUNT(*) FROM inventariodetalles d WHERE d.InventarioID = i.InventarioID) AS Contados,
                   (SELECT COALESCE(SUM(ABS(d.Diferencia)), 0) FROM inventariodetalles d WHERE d.InventarioID = i.InventarioID) AS DifTotal
            FROM inventarios i
            JOIN usuarios u ON i.UsuarioID = u.UsuarioID
            ORDER BY i.InventarioID DESC
            LIMIT 100
        ");
        echo json_encode(['success' => true, 'inventarios' => $stmt->fetchAll()]);
        exit;
    }

    // ---- CREAR ----
    if ($action === 'create') {
        verifyCsrfApi();
        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        $nombre = trim($in['nombre'] ?? '');
        $fechaHora = trim($in['fecha_hora'] ?? '');
        if ($nombre === '') {
            echo json_encode(['success' => false, 'error' => 'Ponle un nombre a la toma de inventario.']);
            exit;
        }
        $fh = $fechaHora !== '' ? date('Y-m-d H:i:s', strtotime($fechaHora)) : date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            INSERT INTO inventarios (UsuarioID, Nombre, FechaHoraInventario, Estado)
            VALUES (:uid, :nombre, :fh, 'Borrador')
        ");
        $stmt->execute([':uid' => $user['id'], ':nombre' => $nombre, ':fh' => $fh]);
        echo json_encode(['success' => true, 'inventario_id' => (int)$pdo->lastInsertId()]);
        exit;
    }

    // ---- VER (cabecera + productos + conteo guardado) ----
    if ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT i.*, u.Nombre AS Usuario FROM inventarios i
            JOIN usuarios u ON i.UsuarioID = u.UsuarioID
            WHERE i.InventarioID = :id
        ");
        $stmt->execute([':id' => $id]);
        $inv = $stmt->fetch();
        if (!$inv) {
            echo json_encode(['success' => false, 'error' => 'Inventario no encontrado.']);
            exit;
        }

        $productos = $pdo->query("
            SELECT p.ProductoID, p.Nombre, p.CodigoBarras, p.CategoriaID, p.Stock,
                   COALESCE(c.Nombre, 'Sin categoría') AS Categoria
            FROM productos p
            LEFT JOIN categorias c ON p.CategoriaID = c.CategoriaID
            WHERE p.Activo = TRUE
            ORDER BY Categoria ASC, p.Nombre ASC
        ")->fetchAll();

        $stmtD = $pdo->prepare("
            SELECT ProductoID, CantidadFisica, StockSistemaAlMomento, Diferencia
            FROM inventariodetalles WHERE InventarioID = :id
        ");
        $stmtD->execute([':id' => $id]);
        $conteo = [];
        foreach ($stmtD->fetchAll() as $d) {
            $conteo[(int)$d['ProductoID']] = $d;
        }

        echo json_encode([
            'success' => true,
            'inventario' => $inv,
            'productos' => $productos,
            'conteo' => $conteo,
        ]);
        exit;
    }

    // ---- GUARDAR CONTEO (parcial, para un Borrador) ----
    if ($action === 'save_conteo') {
        verifyCsrfApi();
        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = (int)($in['inventario_id'] ?? 0);
        $items = $in['items'] ?? [];

        $inv = $pdo->prepare("SELECT Estado FROM inventarios WHERE InventarioID = :id");
        $inv->execute([':id' => $id]);
        $row = $inv->fetch();
        if (!$row) { echo json_encode(['success' => false, 'error' => 'Inventario no encontrado.']); exit; }
        if ($row['Estado'] !== 'Borrador') { echo json_encode(['success' => false, 'error' => 'Este inventario ya fue procesado.']); exit; }

        $pdo->beginTransaction();
        $del = $pdo->prepare("DELETE FROM inventariodetalles WHERE InventarioID = :id AND ProductoID = :pid");
        $ins = $pdo->prepare("
            INSERT INTO inventariodetalles (InventarioID, ProductoID, CantidadFisica, StockSistemaAlMomento, Diferencia)
            VALUES (:id, :pid, :fisica, :sistema, :dif)
        ");
        $getStock = $pdo->prepare("SELECT Stock FROM productos WHERE ProductoID = :pid");

        $n = 0;
        foreach ($items as $it) {
            $pid = (int)($it['producto_id'] ?? 0);
            if ($pid <= 0 || !isset($it['cantidad_fisica']) || $it['cantidad_fisica'] === '') continue;
            $fisica = (float)$it['cantidad_fisica'];
            if ($fisica < 0) continue;
            $getStock->execute([':pid' => $pid]);
            $sistema = (float)$getStock->fetchColumn();
            $del->execute([':id' => $id, ':pid' => $pid]);
            $ins->execute([
                ':id' => $id, ':pid' => $pid, ':fisica' => $fisica,
                ':sistema' => $sistema, ':dif' => $fisica - $sistema,
            ]);
            $n++;
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'guardados' => $n]);
        exit;
    }

    // ---- PROCESAR (genera el ajuste de stock con las diferencias) ----
    if ($action === 'procesar') {
        verifyCsrfApi();
        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = (int)($in['inventario_id'] ?? 0);

        $stmt = $pdo->prepare("SELECT Nombre, Estado FROM inventarios WHERE InventarioID = :id");
        $stmt->execute([':id' => $id]);
        $inv = $stmt->fetch();
        if (!$inv) { echo json_encode(['success' => false, 'error' => 'Inventario no encontrado.']); exit; }
        if ($inv['Estado'] !== 'Borrador') { echo json_encode(['success' => false, 'error' => 'Este inventario ya fue procesado.']); exit; }

        $stmtD = $pdo->prepare("
            SELECT ProductoID, CantidadFisica, StockSistemaAlMomento, Diferencia
            FROM inventariodetalles WHERE InventarioID = :id
        ");
        $stmtD->execute([':id' => $id]);
        $detalles = $stmtD->fetchAll();
        if (empty($detalles)) {
            echo json_encode(['success' => false, 'error' => 'No hay productos contados en este inventario.']);
            exit;
        }

        $conDiferencia = array_filter($detalles, fn($d) => (float)$d['Diferencia'] != 0.0);

        $pdo->beginTransaction();

        $ajusteID = null;
        if (!empty($conDiferencia)) {
            $stmtA = $pdo->prepare("
                INSERT INTO ajustesstock (UsuarioID, Motivo, DocReferencia)
                VALUES (:uid, :motivo, :doc)
            ");
            $stmtA->execute([
                ':uid' => $user['id'],
                ':motivo' => 'Toma de inventario: ' . $inv['Nombre'],
                ':doc' => 'INV-' . $id,
            ]);
            $ajusteID = (int)$pdo->lastInsertId();

            $stmtDA = $pdo->prepare("
                INSERT INTO detalleajustesstock (AjusteStockID, ProductoID, Cantidad, TipoMovimiento)
                VALUES (:aid, :pid, :cant, :tipo)
            ");
            $stmtAdd = $pdo->prepare("UPDATE productos SET Stock = Stock + :cant WHERE ProductoID = :pid");
            $stmtSub = $pdo->prepare("UPDATE productos SET Stock = GREATEST(0, Stock - :cant) WHERE ProductoID = :pid");
            $stmtKIn = $pdo->prepare("
                INSERT INTO kardex (ProductoID, TipoTransaccion, AjusteStockID, CantidadEntrada, StockSaldo, ValorUnitario)
                SELECT :pid, 'AJUSTE_ENTRADA', :aid, :cant, Stock, PrecioVenta FROM productos WHERE ProductoID = :pid2
            ");
            $stmtKOut = $pdo->prepare("
                INSERT INTO kardex (ProductoID, TipoTransaccion, AjusteStockID, CantidadSalida, StockSaldo, ValorUnitario)
                SELECT :pid, 'AJUSTE_SALIDA', :aid, :cant, Stock, PrecioVenta FROM productos WHERE ProductoID = :pid2
            ");

            foreach ($conDiferencia as $d) {
                $pid = (int)$d['ProductoID'];
                $dif = (float)$d['Diferencia'];
                $abs = abs($dif);
                $tipo = $dif > 0 ? 'ENTRADA' : 'SALIDA';
                $stmtDA->execute([':aid' => $ajusteID, ':pid' => $pid, ':cant' => $abs, ':tipo' => $tipo]);
                if ($tipo === 'ENTRADA') {
                    $stmtAdd->execute([':cant' => $abs, ':pid' => $pid]);
                    $stmtKIn->execute([':pid' => $pid, ':aid' => $ajusteID, ':cant' => $abs, ':pid2' => $pid]);
                } else {
                    $stmtSub->execute([':cant' => $abs, ':pid' => $pid]);
                    $stmtKOut->execute([':pid' => $pid, ':aid' => $ajusteID, ':cant' => $abs, ':pid2' => $pid]);
                }
            }
        }

        $pdo->prepare("
            UPDATE inventarios SET Estado = 'Procesado', FechaProcesamiento = NOW()
            WHERE InventarioID = :id
        ")->execute([':id' => $id]);

        $pdo->commit();
        echo json_encode([
            'success' => true,
            'ajuste_id' => $ajusteID,
            'productos_ajustados' => count($conDiferencia),
            'sin_diferencia' => count($detalles) - count($conDiferencia),
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Acción no reconocida.']);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado. Sesión expirada.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $productoID = (int)($_GET['producto_id'] ?? 0);
    if ($productoID <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID de producto inválido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT CodigoID, ProductoID, CodigoBarras, Descripcion, Cantidad, PrecioVenta, DATE_FORMAT(CreadoEn, '%d/%m/%Y %H:%i') AS CreadoEnFmt
            FROM productoscodigos
            WHERE ProductoID = :pid
            ORDER BY CodigoID DESC
        ");
        $stmt->execute([':pid' => $productoID]);
        $codigos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'codigos' => $codigos], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfApi();
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = trim($input['action'] ?? '');

    if ($action === 'add') {
        $productoID = (int)($input['producto_id'] ?? 0);
        $codigoBarras = trim($input['codigo_barras'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $cantidad = isset($input['cantidad']) && (float)$input['cantidad'] > 0 ? (float)$input['cantidad'] : 1.0;
        $precioVenta = !empty($input['precio_venta']) && (int)$input['precio_venta'] > 0 ? (int)$input['precio_venta'] : null;

        if ($productoID <= 0) {
            echo json_encode(['success' => false, 'error' => 'Producto no especificado.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($codigoBarras)) {
            echo json_encode(['success' => false, 'error' => 'Debes ingresar un código de barras.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($cantidad <= 0) {
            echo json_encode(['success' => false, 'error' => 'La cantidad debe ser mayor a cero.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            // Verificar si el producto existe
            $stmtP = $pdo->prepare("SELECT Nombre, CodigoBarras, PrecioVenta FROM productos WHERE ProductoID = :pid");
            $stmtP->execute([':pid' => $productoID]);
            $prod = $stmtP->fetch(PDO::FETCH_ASSOC);
            if (!$prod) {
                echo json_encode(['success' => false, 'error' => 'Producto no encontrado.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Validar que no sea igual al código principal de este u otro producto
            $stmtCheck1 = $pdo->prepare("SELECT Nombre FROM productos WHERE CodigoBarras = :code");
            $stmtCheck1->execute([':code' => $codigoBarras]);
            $match1 = $stmtCheck1->fetch(PDO::FETCH_ASSOC);
            if ($match1) {
                echo json_encode(['success' => false, 'error' => "El código '$codigoBarras' ya está asignado como código principal de '{$match1['Nombre']}'."], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Validar que no exista ya en productoscodigos
            $stmtCheck2 = $pdo->prepare("
                SELECT p.Nombre 
                FROM productoscodigos pc
                JOIN productos p ON pc.ProductoID = p.ProductoID
                WHERE pc.CodigoBarras = :code
            ");
            $stmtCheck2->execute([':code' => $codigoBarras]);
            $match2 = $stmtCheck2->fetch(PDO::FETCH_ASSOC);
            if ($match2) {
                echo json_encode(['success' => false, 'error' => "El código '$codigoBarras' ya está registrado como código secundario de '{$match2['Nombre']}'."], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Insertar código secundario con cantidad y precio de pack opcional
            $stmtIns = $pdo->prepare("
                INSERT INTO productoscodigos (ProductoID, CodigoBarras, Descripcion, Cantidad, PrecioVenta)
                VALUES (:pid, :code, :desc, :cant, :precio)
            ");
            $stmtIns->execute([
                ':pid' => $productoID,
                ':code' => $codigoBarras,
                ':desc' => !empty($descripcion) ? $descripcion : null,
                ':cant' => $cantidad,
                ':precio' => $precioVenta
            ]);

            $detalleCant = $cantidad > 1 ? " (Pack x$cantidad)" : "";
            echo json_encode(['success' => true, 'mensaje' => "Código alternativo '$codigoBarras'$detalleCant asociado exitosamente a {$prod['Nombre']}."], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error al guardar código: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    if ($action === 'update') {
        $codigoID = (int)($input['codigo_id'] ?? 0);
        $codigoBarras = trim($input['codigo_barras'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $cantidad = isset($input['cantidad']) && (float)$input['cantidad'] > 0 ? (float)$input['cantidad'] : 1.0;
        $precioVenta = !empty($input['precio_venta']) && (int)$input['precio_venta'] > 0 ? (int)$input['precio_venta'] : null;

        if ($codigoID <= 0) {
            echo json_encode(['success' => false, 'error' => 'Código no válido para editar.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($codigoBarras)) {
            echo json_encode(['success' => false, 'error' => 'Debes ingresar un código de barras.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($cantidad <= 0) {
            echo json_encode(['success' => false, 'error' => 'La cantidad debe ser mayor a cero.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            // Validar que no sea código principal de ningún producto
            $stmtCheck1 = $pdo->prepare("SELECT Nombre FROM productos WHERE CodigoBarras = :code");
            $stmtCheck1->execute([':code' => $codigoBarras]);
            $match1 = $stmtCheck1->fetch(PDO::FETCH_ASSOC);
            if ($match1) {
                echo json_encode(['success' => false, 'error' => "El código '$codigoBarras' ya es código principal de '{$match1['Nombre']}'."], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Validar que no choque con otro registro en productoscodigos distinto de este
            $stmtCheck2 = $pdo->prepare("SELECT CodigoID FROM productoscodigos WHERE CodigoBarras = :code AND CodigoID != :cid");
            $stmtCheck2->execute([':code' => $codigoBarras, ':cid' => $codigoID]);
            if ($stmtCheck2->fetch()) {
                echo json_encode(['success' => false, 'error' => "El código '$codigoBarras' ya está asignado a otro producto."], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $stmtUpd = $pdo->prepare("
                UPDATE productoscodigos 
                SET CodigoBarras = :code, Descripcion = :desc, Cantidad = :cant, PrecioVenta = :precio
                WHERE CodigoID = :cid
            ");
            $stmtUpd->execute([
                ':code' => $codigoBarras,
                ':desc' => !empty($descripcion) ? $descripcion : null,
                ':cant' => $cantidad,
                ':precio' => $precioVenta,
                ':cid' => $codigoID
            ]);

            echo json_encode(['success' => true, 'mensaje' => 'Código y precio de pack actualizados correctamente.'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    if ($action === 'delete') {
        $codigoID = (int)($input['codigo_id'] ?? 0);
        if ($codigoID <= 0) {
            echo json_encode(['success' => false, 'error' => 'Código no válido para eliminar.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $stmtDel = $pdo->prepare("DELETE FROM productoscodigos WHERE CodigoID = :cid");
            $stmtDel->execute([':cid' => $codigoID]);
            echo json_encode(['success' => true, 'mensaje' => 'Código alternativo eliminado correctamente.'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error al eliminar código: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Acción no reconocida.'], JSON_UNESCAPED_UNICODE);
    exit;
}

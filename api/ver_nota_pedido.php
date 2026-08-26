<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Administrador', 'Supervisor'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de nota de pedido no válido']);
    exit;
}

try {
    $pdo = getDB();

    $stmtNP = $pdo->prepare("
        SELECT np.NotaPedidoID, np.ProveedorID, np.NumeroDocumento, np.Estado, np.FechaPedido,
               np.NotaPedidoOrigenID, p.RazonSocial AS Proveedor
        FROM notaspedido np
        JOIN proveedores p ON np.ProveedorID = p.ProveedorID
        WHERE np.NotaPedidoID = :id
    ");
    $stmtNP->execute([':id' => $id]);
    $notaPedido = $stmtNP->fetch();

    if (!$notaPedido) {
        echo json_encode(['success' => false, 'error' => 'Nota de pedido no encontrada']);
        exit;
    }

    $stmtD = $pdo->prepare("
        SELECT dnp.ProductoID, dnp.CantidadPedida, dnp.CostoAcordado, pr.Nombre
        FROM detallenotaspedido dnp
        JOIN productos pr ON dnp.ProductoID = pr.ProductoID
        WHERE dnp.NotaPedidoID = :id
    ");
    $stmtD->execute([':id' => $id]);
    $detalles = $stmtD->fetchAll();

    // Compras que recibieron (total o parcialmente) esta nota
    $stmtCompras = $pdo->prepare("
        SELECT CompraID, FechaCompra, MontoTotal FROM compras WHERE NotaPedidoID = :id ORDER BY CompraID ASC
    ");
    $stmtCompras->execute([':id' => $id]);
    $comprasAsociadas = $stmtCompras->fetchAll();

    // Nota de pedido de la que ésta se originó (si nació de un faltante)
    $notaOrigen = null;
    if ($notaPedido['NotaPedidoOrigenID']) {
        $stmtOrigen = $pdo->prepare("SELECT NotaPedidoID, Estado FROM notaspedido WHERE NotaPedidoID = :id");
        $stmtOrigen->execute([':id' => $notaPedido['NotaPedidoOrigenID']]);
        $notaOrigen = $stmtOrigen->fetch();
    }

    // Nota de pedido generada a partir de esta (si quedó un faltante sin recibir)
    $stmtDerivada = $pdo->prepare("SELECT NotaPedidoID, Estado FROM notaspedido WHERE NotaPedidoOrigenID = :id");
    $stmtDerivada->execute([':id' => $id]);
    $notaDerivada = $stmtDerivada->fetch();

    echo json_encode([
        'success' => true,
        'nota_pedido' => [
            'id' => $notaPedido['NotaPedidoID'],
            'proveedor_id' => $notaPedido['ProveedorID'],
            'proveedor' => $notaPedido['Proveedor'],
            'numero_documento' => $notaPedido['NumeroDocumento'],
            'estado' => $notaPedido['Estado'],
            'fecha' => date('d/m/Y H:i', strtotime($notaPedido['FechaPedido']))
        ],
        'detalles' => array_map(function ($d) {
            return [
                'producto_id' => (int)$d['ProductoID'],
                'nombre' => $d['Nombre'],
                'cantidad_pedida' => (float)$d['CantidadPedida'],
                'costo_acordado' => (int)$d['CostoAcordado']
            ];
        }, $detalles),
        'compras_asociadas' => array_map(function ($c) {
            return [
                'compra_id' => (int)$c['CompraID'],
                'fecha' => date('d/m/Y H:i', strtotime($c['FechaCompra'])),
                'monto_total' => (int)$c['MontoTotal']
            ];
        }, $comprasAsociadas),
        'nota_origen' => $notaOrigen ? ['id' => (int)$notaOrigen['NotaPedidoID'], 'estado' => $notaOrigen['Estado']] : null,
        'nota_derivada' => $notaDerivada ? ['id' => (int)$notaDerivada['NotaPedidoID'], 'estado' => $notaDerivada['Estado']] : null
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();

// Parámetros de filtrado
$desde = trim($_GET['desde'] ?? ($_GET['fecha'] ?? date('Y-m-d')));
$hasta = trim($_GET['hasta'] ?? ($_GET['fecha'] ?? date('Y-m-d')));
$q = trim($_GET['q'] ?? '');
$tipoDoc = trim($_GET['tipo_doc'] ?? 'Todos');
$estado = trim($_GET['estado'] ?? 'Todos');

// Validación básica de fechas
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) $hasta = date('Y-m-d');
if ($desde > $hasta) {
    $temp = $desde;
    $desde = $hasta;
    $hasta = $temp;
}

// Construcción de consulta dinámica
$where = ["DATE(v.FechaVenta) BETWEEN :desde AND :hasta"];
$params = [
    ':desde' => $desde,
    ':hasta' => $hasta
];

if ($q !== '') {
    // Si viene con prefijo # o número limpio
    $cleanQ = ltrim($q, '#');
    $where[] = "(v.VentaID = :qid OR v.Folio = :qfolio OR dte.Folio = :qdtefolio OR c.Nombre LIKE :qname OR c.RutCuerpo LIKE :qrut)";
    $params[':qid'] = is_numeric($cleanQ) ? (int)$cleanQ : 0;
    $params[':qfolio'] = is_numeric($cleanQ) ? (int)$cleanQ : 0;
    $params[':qdtefolio'] = is_numeric($cleanQ) ? (int)$cleanQ : 0;
    $params[':qname'] = "%$q%";
    $params[':qrut'] = "%$cleanQ%";
}

if ($tipoDoc !== 'Todos' && in_array($tipoDoc, ['Boleta', 'Factura', 'Ticket'], true)) {
    $where[] = "v.TipoDocumento = :tdoc";
    $params[':tdoc'] = $tipoDoc;
}

if ($estado !== 'Todos' && in_array($estado, ['Completada', 'Anulada'], true)) {
    $where[] = "v.Estado = :est";
    $params[':est'] = $estado;
}

$whereSql = implode(' AND ', $where);

$query = "
    SELECT v.VentaID, v.FechaVenta, v.TipoDocumento, v.TipoDte, v.MontoNeto, v.MontoIva, 
           v.DescuentoGlobal, v.MontoTotal, v.MontoPagado, v.Vuelto, v.Estado,
           COALESCE(dte.Folio, v.Folio) AS FolioDoc,
           COALESCE(dte.PdfUrl, v.DtePdfPath) AS DtePdfUrl,
           dte.EstadoSii AS DteEstadoSii,
           u.Nombre AS Cajero,
           c.Nombre AS ClienteNombre, c.RutCuerpo, c.RutDv,
           (SELECT GROUP_CONCAT(DISTINCT pv.MetodoPago SEPARATOR ' + ') 
            FROM pagosventa pv WHERE pv.VentaID = v.VentaID) AS MetodosPago,
           (SELECT GROUP_CONCAT(CONCAT(p.Nombre, ' (x', dv.Cantidad, ')') SEPARATOR ', ') 
            FROM detalleventas dv 
            JOIN productos p ON dv.ProductoID = p.ProductoID 
            WHERE dv.VentaID = v.VentaID) AS ProductosDetalle,
           (SELECT COUNT(*) FROM detalleventas dv WHERE dv.VentaID = v.VentaID) AS TotalItems
    FROM ventas v
    JOIN turnos t ON v.TurnoID = t.TurnoID
    JOIN usuarios u ON t.UsuarioID = u.UsuarioID
    LEFT JOIN clientes c ON v.ClienteID = c.ClienteID
    LEFT JOIN (
        SELECT VentaID, Folio, PdfUrl, EstadoSii 
        FROM dte_emitidos 
        WHERE DteID IN (SELECT MAX(DteID) FROM dte_emitidos GROUP BY VentaID)
    ) dte ON v.VentaID = dte.VentaID
    WHERE $whereSql
    ORDER BY v.VentaID DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cálculo de Métricas (KPIs)
$totalRecaudado = 0;
$totalVentasCount = 0;
$totalAnuladasCount = 0;
$totalAnuladoMonto = 0;

foreach ($ventas as $v) {
    if ($v['Estado'] === 'Completada') {
        $totalRecaudado += (int)$v['MontoTotal'];
        $totalVentasCount++;
    } else {
        $totalAnuladasCount++;
        $totalAnuladoMonto += (int)$v['MontoTotal'];
    }
}
$ticketPromedio = $totalVentasCount > 0 ? (int)round($totalRecaudado / $totalVentasCount) : 0;

// Cargar datos de la empresa para impresión y encabezados
$stmtCfgLocal = $pdo->query("
    SELECT Clave, Valor FROM configuraciones
    WHERE Clave IN ('MINIMARKET_NOMBRE', 'MINIMARKET_RUT', 'MINIMARKET_DIRECCION', 'MINIMARKET_GIRO', 'MINIMARKET_TELEFONO', 'TICKET_PIE_PAGINA')
");
$cfgLocal = [];
while ($row = $stmtCfgLocal->fetch(PDO::FETCH_ASSOC)) {
    $cfgLocal[$row['Clave']] = $row['Valor'];
}

include __DIR__ . '/views/ventas.view.php';
require_once __DIR__ . '/includes/footer.php';

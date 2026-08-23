<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$user = currentUser();
$message = '';
$error = '';

// Procesar Generación de Cierre Z
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $cajaID = (int)($_POST['caja_id'] ?? 1);

    try {
        $pdo->beginTransaction();

        // Bloquear la caja mientras se calcula el cierre, para que dos cierres
        // simultáneos de la misma caja no generen el mismo número de folio Z.
        $stmtLock = $pdo->prepare("SELECT CajaID FROM Cajas WHERE CajaID = :caja FOR UPDATE");
        $stmtLock->execute([':caja' => $cajaID]);
        if (!$stmtLock->fetch()) {
            throw new Exception("La caja #$cajaID no existe.");
        }

        // Obtener último número Z de la caja
        $stmtZ = $pdo->prepare("SELECT COALESCE(MAX(NumeroZ), 0) + 1 FROM ReportesZ WHERE CajaID = :caja");
        $stmtZ->execute([':caja' => $cajaID]);
        $nextZ = (int)$stmtZ->fetchColumn();

        // Obtener fecha del último Z o fecha inicial
        $stmtLastZ = $pdo->prepare("SELECT FechaEmision FROM ReportesZ WHERE CajaID = :caja ORDER BY ReporteZID DESC LIMIT 1");
        $stmtLastZ->execute([':caja' => $cajaID]);
        $fechaInicio = $stmtLastZ->fetchColumn() ?: date('Y-m-d 00:00:00');

        // Calcular totales de ventas no incluidas en un Z anterior
        $stmtTot = $pdo->prepare("
            SELECT
                COALESCE(SUM(v.MontoNeto), 0) AS Neto,
                COALESCE(SUM(v.MontoIva), 0) AS Iva,
                COALESCE(SUM(v.MontoExento), 0) AS Exento,
                COALESCE(SUM(v.MontoTotal), 0) AS Total,
                COUNT(CASE WHEN v.TipoDocumento = 'Boleta' THEN 1 END) AS Boletas,
                COUNT(CASE WHEN v.TipoDocumento = 'Factura' THEN 1 END) AS Facturas,
                MIN(CASE WHEN v.TipoDocumento = 'Boleta' THEN v.Folio END) AS PrimerFolioB,
                MAX(CASE WHEN v.TipoDocumento = 'Boleta' THEN v.Folio END) AS UltimoFolioB,
                MIN(CASE WHEN v.TipoDocumento = 'Factura' THEN v.Folio END) AS PrimerFolioF,
                MAX(CASE WHEN v.TipoDocumento = 'Factura' THEN v.Folio END) AS UltimoFolioF
            FROM Ventas v
            JOIN Turnos t ON v.TurnoID = t.TurnoID
            WHERE v.ReporteZID IS NULL AND v.Estado = 'Completada' AND t.CajaID = :caja
        ");
        $stmtTot->execute([':caja' => $cajaID]);
        $tot = $stmtTot->fetch();

        // Insertar Reporte Z
        $stmtInsZ = $pdo->prepare("
            INSERT INTO ReportesZ (
                CajaID, NumeroZ, UsuarioID, FechaInicio, MontoNeto, MontoIva, MontoExento, MontoTotal,
                CantidadBoletas, CantidadFacturas, PrimerFolioBoleta, UltimoFolioBoleta, PrimerFolioFactura, UltimoFolioFactura
            ) VALUES (
                :caja, :numz, :uid, :finicio, :neto, :iva, :exento, :total,
                :boletas, :facturas, :pfb, :ufb, :pff, :uff
            )
        ");
        $stmtInsZ->execute([
            ':caja' => $cajaID,
            ':numz' => $nextZ,
            ':uid' => $user['id'],
            ':finicio' => $fechaInicio,
            ':neto' => $tot['Neto'],
            ':iva' => $tot['Iva'],
            ':exento' => $tot['Exento'],
            ':total' => $tot['Total'],
            ':boletas' => $tot['Boletas'],
            ':facturas' => $tot['Facturas'],
            ':pfb' => $tot['PrimerFolioB'],
            ':ufb' => $tot['UltimoFolioB'],
            ':pff' => $tot['PrimerFolioF'],
            ':uff' => $tot['UltimoFolioF']
        ]);
        $reporteZID = $pdo->lastInsertId();

        // Marcar ventas con el ID de este Reporte Z (solo las de esta caja)
        $stmtUpdV = $pdo->prepare("
            UPDATE Ventas v
            JOIN Turnos t ON v.TurnoID = t.TurnoID
            SET v.ReporteZID = :zid
            WHERE v.ReporteZID IS NULL AND v.Estado = 'Completada' AND t.CajaID = :caja
        ");
        $stmtUpdV->execute([':zid' => $reporteZID, ':caja' => $cajaID]);

        $pdo->commit();
        $message = "Reporte Z #$nextZ generado exitosamente por un total de " . formatCLP($tot['Total']) . ".";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = 'Error al generar Cierre Z: ' . $e->getMessage();
    }
}

// Historial de Cierres Z
$stmtReportesZ = $pdo->query("
    SELECT z.*, u.Nombre AS Usuario, c.Nombre AS CajaName
    FROM ReportesZ z
    JOIN Usuarios u ON z.UsuarioID = u.UsuarioID
    JOIN Cajas c ON z.CajaID = c.CajaID
    ORDER BY z.ReporteZID DESC LIMIT 20
");
$cierresZ = $stmtReportesZ->fetchAll();

include __DIR__ . '/views/reportes_z.view.php';
require_once __DIR__ . '/includes/footer.php';

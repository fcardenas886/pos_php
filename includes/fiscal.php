<?php
// Genera un Cierre Z para una caja: consolida en un nuevo folio Z las ventas
// completadas de esa caja que aún no pertenecen a ningún Z. El caller debe
// llamarla dentro de una transacción ya abierta (usa FOR UPDATE para
// serializar cierres concurrentes de la misma caja).
function generarCierreZ(PDO $pdo, int $cajaID, int $usuarioID): array {
    $stmtLock = $pdo->prepare("SELECT CajaID FROM cajas WHERE CajaID = :caja FOR UPDATE");
    $stmtLock->execute([':caja' => $cajaID]);
    if (!$stmtLock->fetch()) {
        throw new Exception("La caja #$cajaID no existe.");
    }

    $stmtZ = $pdo->prepare("SELECT COALESCE(MAX(NumeroZ), 0) + 1 FROM reportesz WHERE CajaID = :caja");
    $stmtZ->execute([':caja' => $cajaID]);
    $nextZ = (int)$stmtZ->fetchColumn();

    $stmtLastZ = $pdo->prepare("SELECT FechaEmision FROM reportesz WHERE CajaID = :caja ORDER BY ReporteZID DESC LIMIT 1");
    $stmtLastZ->execute([':caja' => $cajaID]);
    $fechaInicio = $stmtLastZ->fetchColumn() ?: date('Y-m-d 00:00:00');

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
        FROM ventas v
        JOIN turnos t ON v.TurnoID = t.TurnoID
        WHERE v.ReporteZID IS NULL AND v.Estado = 'Completada' AND t.CajaID = :caja
    ");
    $stmtTot->execute([':caja' => $cajaID]);
    $tot = $stmtTot->fetch();

    $stmtInsZ = $pdo->prepare("
        INSERT INTO reportesz (
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
        ':uid' => $usuarioID,
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

    $stmtUpdV = $pdo->prepare("
        UPDATE ventas v
        JOIN turnos t ON v.TurnoID = t.TurnoID
        SET v.ReporteZID = :zid
        WHERE v.ReporteZID IS NULL AND v.Estado = 'Completada' AND t.CajaID = :caja
    ");
    $stmtUpdV->execute([':zid' => $reporteZID, ':caja' => $cajaID]);

    return ['numeroZ' => $nextZ, 'reporteZID' => $reporteZID, 'total' => (int)$tot['Total']];
}

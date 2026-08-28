-- Registro de DTE emitidos (Boleta / Factura / Nota de Credito electronica) a
-- traves del modulo includes/sii/. Guarda folio, track y URLs de PDF/XML que
-- devuelve el proveedor (mock, OpenFactura, etc.).
-- Requerida por: includes/sii/SiiFacturacion.php, api/registrar_venta.php

CREATE TABLE IF NOT EXISTS dte_emitidos (
  DteID INT NOT NULL AUTO_INCREMENT,
  VentaID INT DEFAULT NULL,
  DevolucionID INT DEFAULT NULL,
  TipoDocumento VARCHAR(20) NOT NULL,
  Folio INT NOT NULL,
  TrackID VARCHAR(100) DEFAULT NULL,
  EstadoSii VARCHAR(50) DEFAULT 'Pendiente',
  PdfUrl VARCHAR(255) DEFAULT NULL,
  XmlUrl VARCHAR(255) DEFAULT NULL,
  FechaEmision DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (DteID),
  KEY VentaID (VentaID),
  KEY DevolucionID (DevolucionID),
  CONSTRAINT dte_emitidos_ibfk_1 FOREIGN KEY (VentaID) REFERENCES ventas (VentaID) ON DELETE SET NULL,
  CONSTRAINT dte_emitidos_ibfk_2 FOREIGN KEY (DevolucionID) REFERENCES devoluciones (DevolucionID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

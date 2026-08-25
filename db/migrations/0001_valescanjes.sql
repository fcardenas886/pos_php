-- No hay sistema de migraciones en este proyecto: cada archivo en db/migrations/
-- documenta un cambio de esquema que ya se aplico manualmente a la base de datos
-- de desarrollo. Al preparar un servidor nuevo (o el VPS de produccion), correr
-- estos scripts en orden contra la base de datos antes de desplegar el codigo
-- que depende de ellos.
--
-- Esta tabla registra el canje de Vales de Devolucion (Nota de Credito) contra
-- una venta futura, para que valesdevolucion.MontoDisponible se pueda ir
-- descontando con trazabilidad de en que venta(s) se uso cada vale.
-- Requerida por: api/registrar_venta.php (metodo de pago 'Vale Devolucion').

CREATE TABLE IF NOT EXISTS valescanjes (
  CanjeID INT NOT NULL AUTO_INCREMENT,
  ValeID INT NOT NULL,
  VentaID INT NOT NULL,
  Monto INT NOT NULL,
  FechaCanje DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (CanjeID),
  KEY FK_Canjes_Vale (ValeID),
  KEY FK_Canjes_Venta (VentaID),
  CONSTRAINT FK_Canjes_Vale FOREIGN KEY (ValeID) REFERENCES valesdevolucion (ValeID),
  CONSTRAINT FK_Canjes_Venta FOREIGN KEY (VentaID) REFERENCES ventas (VentaID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

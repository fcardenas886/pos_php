-- Ajustes de Stock con proveedor y documento de referencia: permite registrar
-- de que proveedor vino la mercaderia de un ajuste de ENTRADA (o a que doc se
-- asocia una SALIDA/devolucion a proveedor), sin pasar por Recepcion de Compras.
-- Requerida por: ajustes.php, api/registrar_ajuste.php

ALTER TABLE ajustesstock
  ADD COLUMN ProveedorID INT DEFAULT NULL,
  ADD COLUMN DocReferencia VARCHAR(50) DEFAULT NULL,
  ADD CONSTRAINT FK_AjustesStock_Proveedores FOREIGN KEY (ProveedorID) REFERENCES proveedores (ProveedorID);

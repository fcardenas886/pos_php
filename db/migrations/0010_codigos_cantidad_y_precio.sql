-- Migración 0010: Cantidad (Factor de Conversión) y Precio para Códigos de Barra Alternativos (Packs/Cajas)
ALTER TABLE productoscodigos 
    ADD COLUMN Cantidad DECIMAL(10,3) NOT NULL DEFAULT 1.000 AFTER Descripcion,
    ADD COLUMN PrecioVenta INT NULL DEFAULT NULL AFTER Cantidad;

ALTER TABLE detalleventas
    ADD COLUMN NombreItem VARCHAR(200) NULL DEFAULT NULL AFTER ProductoID,
    ADD COLUMN FactorConversion DECIMAL(10,3) NOT NULL DEFAULT 1.000 AFTER Cantidad;

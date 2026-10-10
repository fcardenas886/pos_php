-- Migración 0012: Congelar CostoUnitario en DetalleAjustesStock para valorización precisa de mermas y pérdidas
ALTER TABLE `detalleajustesstock` 
ADD COLUMN IF NOT EXISTS `CostoUnitario` INT NOT NULL DEFAULT 0 AFTER `Cantidad`;

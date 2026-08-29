-- Productos pesables (venta por peso via balanza): EsPesable marca el producto y
-- CodigoPLU es el codigo de 4 digitos que la balanza imprime en la etiqueta
-- EAN-13. El POS parsea el codigo de balanza (prefijo 20 + PLU + peso/precio) y
-- resuelve el producto por CodigoPLU.
-- Requerida por: productos.php, api/buscar_producto.php, assets/js/pos.js

ALTER TABLE productos
  ADD COLUMN EsPesable TINYINT(1) DEFAULT 0,
  ADD COLUMN CodigoPLU VARCHAR(4) DEFAULT NULL,
  ADD UNIQUE KEY CodigoPLU (CodigoPLU);

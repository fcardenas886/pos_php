-- Migración 0011: CodigoPLU admite hasta 8 dígitos.
-- Con balanza sin etiqueta el PLU es solo un código corto que el cajero digita en
-- el POS, y se autogenera como ProductoID * 100 (puede superar los 4 dígitos que
-- exige la etiqueta EAN-13). Con balanza de etiqueta se sigue validando 4 dígitos.
-- Requerida por: productos.php, api/generar_codigo_plu.php
ALTER TABLE productos MODIFY COLUMN CodigoPLU VARCHAR(8) DEFAULT NULL;

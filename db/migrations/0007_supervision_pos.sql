-- Migracion: 0007_supervision_pos.sql
-- Parametros de supervision y control para el punto de venta (POS)
-- Permite configurar si un cajero requiere clave de supervisor para:
-- 1. Cancelar / vaciar venta en curso (POS_REQ_SUPERVISOR_CANCELAR)
-- 2. Quitar un producto de la grilla del carrito (POS_REQ_SUPERVISOR_ELIMINAR_ITEM)
-- 3. Aplicar descuento por sobre un porcentaje maximo permitido (POS_DESCUENTO_MAX_PORC)

INSERT INTO configuraciones (Clave, Valor, Descripcion)
VALUES 
  ('POS_REQ_SUPERVISOR_CANCELAR', 'SI', 'Requerir clave de supervisor para cancelar o vaciar venta en proceso en POS'),
  ('POS_REQ_SUPERVISOR_ELIMINAR_ITEM', 'SI', 'Requerir clave de supervisor para eliminar un producto del carrito en POS'),
  ('POS_DESCUENTO_MAX_PORC', '5', 'Porcentaje (%) maximo de descuento permitido en caja sin clave de supervisor')
ON DUPLICATE KEY UPDATE 
  Descripcion = VALUES(Descripcion);

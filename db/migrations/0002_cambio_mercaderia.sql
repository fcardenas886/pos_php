-- Agrega "Cambio de Mercaderia" como MetodoDevolucion valido, ademas de
-- Efectivo/Tarjeta/Nota de Credito. Funcionalmente genera un vale igual que
-- "Nota de Credito"; la diferencia es solo la etiqueta para reportes (el
-- cliente se llevo otro producto en el momento, no solo un vale para despues).
-- Requerida por: api/registrar_devolucion.php

ALTER TABLE devoluciones DROP CONSTRAINT CK_Devoluciones_Metodo;
ALTER TABLE devoluciones ADD CONSTRAINT CK_Devoluciones_Metodo
  CHECK (MetodoDevolucion IN ('Efectivo','Tarjeta','Nota de Credito','Cambio de Mercaderia'));

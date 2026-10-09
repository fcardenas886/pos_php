-- Migración 0011: permitir stock negativo a nivel de base de datos.
-- La opción "Vender con stock negativo" (PERMITIR_STOCK_NEGATIVO) ya se valida en
-- api/registrar_venta.php y api/sincronizar_offline.php; estos CHECK impedían que
-- la venta se grabara aunque la configuración lo permitiera (error 3819).
-- Con la opción en "No permitir", la aplicación sigue bloqueando la venta sin stock.
ALTER TABLE productos DROP CHECK CK_Productos_Stock;
ALTER TABLE kardex DROP CHECK CK_Kardex_Saldo;

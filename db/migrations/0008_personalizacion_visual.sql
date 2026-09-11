-- Migración 0008: Personalización Visual, Marca y Diseño de Grilla POS
INSERT INTO configuraciones (Clave, Valor) VALUES 
('TEMA_MODO', 'dark'),
('TEMA_COLOR_ACENTO', 'indigo'),
('MINIMARKET_LOGO_URL', ''),
('POS_DISENO_GRID', 'estandar'),
('POS_LAYOUT_MODO', 'supermercado')
ON DUPLICATE KEY UPDATE Clave = Clave;

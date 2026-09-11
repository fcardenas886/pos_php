-- Migración 0009: Múltiples Códigos de Barra por Producto (v3.0.0)
CREATE TABLE IF NOT EXISTS productoscodigos (
    CodigoID INT AUTO_INCREMENT PRIMARY KEY,
    ProductoID INT NOT NULL,
    CodigoBarras VARCHAR(50) NOT NULL,
    Descripcion VARCHAR(100) NULL,
    CreadoEn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_prodcodigos_producto FOREIGN KEY (ProductoID) REFERENCES productos(ProductoID) ON DELETE CASCADE,
    UNIQUE KEY uq_codigo_barras_adicional (CodigoBarras)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

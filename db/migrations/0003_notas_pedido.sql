-- Notas de Pedido: lo que se acuerda con el proveedor (cantidad, costo) ANTES de
-- que llegue la mercaderia. No toca stock ni costo hasta que se recibe de verdad
-- via Recepcion de Compras (tabla compras/detallecompras).
--
-- Cuando se recibe contra una Nota de Pedido, esta pasa a "Recibida" completa
-- (no hay estado "Parcial" que quede abierto); si algo no llego, se genera una
-- Nota de Pedido nueva (NotaPedidoOrigenID apunta a la original) solo con lo
-- que falto, en vez de dejar la original en un limbo.
--
-- Requerida por: notaspedido.php, api/registrar_nota_pedido.php,
-- api/ver_nota_pedido.php, api/registrar_compra.php (recepcion contra nota).

CREATE TABLE IF NOT EXISTS notaspedido (
  NotaPedidoID INT NOT NULL AUTO_INCREMENT,
  ProveedorID INT NOT NULL,
  UsuarioID INT NOT NULL,
  FechaPedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  NumeroDocumento VARCHAR(50) DEFAULT NULL,
  Estado VARCHAR(15) NOT NULL DEFAULT 'Pendiente',
  NotaPedidoOrigenID INT DEFAULT NULL,
  Observaciones VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (NotaPedidoID),
  KEY FK_NotasPedido_Proveedores (ProveedorID),
  KEY FK_NotasPedido_Usuarios (UsuarioID),
  KEY FK_NotasPedido_Origen (NotaPedidoOrigenID),
  CONSTRAINT FK_NotasPedido_Proveedores FOREIGN KEY (ProveedorID) REFERENCES proveedores (ProveedorID),
  CONSTRAINT FK_NotasPedido_Usuarios FOREIGN KEY (UsuarioID) REFERENCES usuarios (UsuarioID),
  CONSTRAINT FK_NotasPedido_Origen FOREIGN KEY (NotaPedidoOrigenID) REFERENCES notaspedido (NotaPedidoID),
  CONSTRAINT CK_NotasPedido_Estado CHECK (Estado IN ('Pendiente','Recibida','Cancelada'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE IF NOT EXISTS detallenotaspedido (
  DetalleNotaPedidoID INT NOT NULL AUTO_INCREMENT,
  NotaPedidoID INT NOT NULL,
  ProductoID INT NOT NULL,
  CantidadPedida DECIMAL(10,3) NOT NULL,
  CostoAcordado INT NOT NULL,
  PRIMARY KEY (DetalleNotaPedidoID),
  KEY FK_DetalleNotasPedido_NotasPedido (NotaPedidoID),
  KEY FK_DetalleNotasPedido_Productos (ProductoID),
  CONSTRAINT FK_DetalleNotasPedido_NotasPedido FOREIGN KEY (NotaPedidoID) REFERENCES notaspedido (NotaPedidoID),
  CONSTRAINT FK_DetalleNotasPedido_Productos FOREIGN KEY (ProductoID) REFERENCES productos (ProductoID),
  CONSTRAINT CK_DetalleNotasPedido_Cant CHECK (CantidadPedida > 0),
  CONSTRAINT CK_DetalleNotasPedido_Costo CHECK (CostoAcordado >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

ALTER TABLE compras ADD COLUMN NotaPedidoID INT DEFAULT NULL AFTER ProveedorID;
ALTER TABLE compras ADD CONSTRAINT FK_Compras_NotaPedido FOREIGN KEY (NotaPedidoID) REFERENCES notaspedido (NotaPedidoID);

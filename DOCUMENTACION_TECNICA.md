# 📘 Documentación Técnica - Sistema Minimarket POS
**Versión:** 3.3.0  
**Fecha de Respaldo:** 2026-09-09  
**Ubicación:** minimarket-php (Respaldo en minimarket-php-backup-pre-pwa)  
**Arquitectura:** PHP 8.1+ / MySQL (InnoDB) / Vanilla JavaScript / CSS Custom Theme  

---

## 1. Propósito del Sistema
El sistema es una solución integral de **Punto de Venta (POS), Gestión Comercial, Inventario y Facturación Electrónica** diseñada específicamente para el comercio minorista chileno (minimarkets, botillerías, almacenes y distribuidoras de abarrotes).

Permite administrar el ciclo completo del negocio:
* Venta ágil en caja mediante escáner de código de barras y balanza digital.
* Control estricto de turnos de cajero, arqueos ciegos y movimientos de efectivo.
* Manejo de inventario con múltiples códigos de barra por producto (packs, cajas, unidades).
* Precios diferenciados, promociones automáticas (Multibuy, descuentos, ofertas).
* Crédito a clientes de confianza (Fiados) y sistema de fidelización por puntos.
* Emisión de Boleta y Factura Electrónica conforme a las normativas del SII (Chile).

---

## 2. Stack Tecnológico

| Capa | Tecnología / Herramienta | Descripción |
| :--- | :--- | :--- |
| **Backend** | **PHP 8.1+** (Nativo / Procedural estructurado) | Sin dependencias de frameworks pesados, optimizado para alto rendimiento y baja latencia en Apache/Nginx. |
| **Base de Datos** | **MySQL 5.7 / 8.0 / MariaDB** | Motor **InnoDB**, codificación utf8mb4_unicode_ci, integridad referencial con claves foráneas y transacciones ACID. |
| **Conexión DB** | **PDO (PHP Data Objects)** | Configurado en modo estricto de excepciones (ATTR_ERRMODE => ERRMODE_EXCEPTION) y consultas preparadas obligatorias. |
| **Frontend** | **HTML5 Semántico + JavaScript Vanilla (ES6+)** | Sin frameworks como React/Vue; manipulación directa del DOM para máxima velocidad y compatibilidad en navegadores POS. |
| **Diseño / Estilos** | **CSS3 Personalizado (CSS Variables)** | Soporte nativo para modo oscuro/claro, diseño responsivo, adaptado a monitores táctiles y pantallas de 1024px o superiores. |
| **Iconografía / Gráficos** | **FontAwesome 6 + Chart.js** | Visualización de KPIs ejecutivos, gráficos de ventas y mapa de horas peak. |
| **Seguridad** | **BCrypt + Tokens CSRF + Auth Session** | Encriptación de contraseñas de alta seguridad, protección contra ataques CSRF en todas las APIs POST, y aislamiento de sesiones de cajero. |

---

## 3. Estructura del Proyecto

`
minimarket-php/
├── api/                           # Endpoints JSON RESTful para consumo asíncrono
│   ├── actualizar_precios_compra.php
│   ├── anular_venta.php
│   ├── auditar_turno.php
│   ├── autorizar_supervisor.php    # Validación de PIN/clave para cancelaciones y descuentos
│   ├── buscar_producto.php         # Búsqueda rápida por EAN, PLU, Nombre y Códigos Alternativos
│   ├── codigos_producto.php        # Gestión de códigos secundarios y packs
│   ├── cotizaciones.php            # Pausar y recuperar ventas pendientes
│   ├── desbloquear_caja.php        # Desbloqueo de pantalla (Lock Screen)
│   ├── detalle_cuenta_cliente.php  # Historial de fiados y abonos
│   ├── guardar_cambio_precios.php  # Actualización masiva de precios y márgenes
│   ├── inventario.php
│   ├── movimiento_caja.php         # Egresos e ingresos manuales de efectivo
│   ├── reembolsar_vale.php
│   ├── registrar_ajuste.php        # Ajustes de stock por merma, vencimiento o conteo
│   ├── registrar_compra.php        # Ingreso de facturas y mercadería de proveedores
│   ├── registrar_devolucion.php    # Emisión de vales de cambio
│   ├── registrar_nota_pedido.php
│   ├── registrar_venta.php         # ⭐ Core de venta: transacciones, stock y medios de pago
│   ├── ver_nota_pedido.php
│   ├── ver_venta.php               # Detalle de venta y reimpresión de ticket
│   └── verificar_vale.php
│
├── assets/
│   ├── css/
│   │   └── style.css              # Estilos globales y variables de diseño
│   └── js/
│       ├── pos.js                 # ⭐ Lógica del POS: carrito, cobros, balanza, teclado
│       └── ui.js                  # Modales, toasts, alertas y componentes de interfaz
│
├── config/
│   └── database.php               # Conexión PDO, constantes del sistema y changelog
│
├── db/
│   └── migrations/                # Scripts de evolución de esquema SQL (0001 a 0010)
│       ├── 0001_valescanjes.sql
│       ├── 0002_cambio_mercaderia.sql
│       ├── 0003_notas_pedido.sql
│       ├── 0004_ajustesstock_proveedor.sql
│       ├── 0005_dte_emitidos.sql
│       ├── 0006_productos_pesable.sql
│       ├── 0007_supervision_pos.sql
│       ├── 0008_personalizacion_visual.sql
│       ├── 0009_multiples_codigos_y_bloqueo.sql
│       └── 0010_codigos_cantidad_y_precio.sql
│
├── includes/                      # Componentes comunes de servidor
│   ├── auth.php                   # Funciones de sesión, permisos y verificación CSRF
│   ├── header.php                 # Menú superior y barra de navegación
│   └── footer.php                 # Pie de página y versión
│
├── views/                         # Plantillas de renderizado desacopladas (.view.php)
│   ├── pos.view.php               # Interfaz de Punto de Venta
│   ├── caja.view.php              # Control de arqueos y turnos
│   ├── ventas.view.php            # Consulta y filtros de ventas históricas
│   ├── productos.view.php         # Maestro de artículos
│   ├── clientes.view.php          # Cuentas corrientes y cartera de clientes
│   ├── reportes.view.php          # Centro de analítica y business intelligence
│   └── ...
│
├── caja.php                       # Controlador de flujo de turnos
├── pos.php                        # Controlador principal del punto de venta
├── ventas.php                     # Historial de ventas y reimpresión
├── productos.php                  # Catálogo de productos
├── actualizar_precios.php         # Herramienta rápida de márgenes y flejes
├── reportes.php                   # Reportes gerenciales y exportación a Excel
└── index.php                      # Dashboard principal / Dashboard de inicio
`

---

## 4. Lógica de Negocio y Algoritmos Críticos

### A. Núcleo de Ventas (pi/registrar_venta.php)
1. **Transaccionalidad Estricta:** Todo el proceso de venta corre dentro de una transacción BEGIN TRANSACTION ... COMMIT con bloqueos pesimistas SELECT ... FOR UPDATE sobre los productos vendidos para evitar colisiones de stock concurrente.
2. **Validación de Turno:** La venta se rechaza si el cajero no tiene un turno con estado 'Abierto' en la tabla 	urnos.
3. **Múltiples Medios de Pago:** Soporta pagos simples y mixtos (Efectivo, Tarjeta Debito, Tarjeta Credito, Transferencia, Credito Interno / Fiado, Puntos, Vale Devolucion).
4. **Descuento de Stock por Factor:**
   * Si se vende un producto individual: unidadesFisicas = cantidad.
   * Si se vende un Six Pack o Caja (código alternativo): unidadesFisicas = cantidad * factor. Se descuenta el inventario real de la unidad base.
5. **Generación de Deuda / Puntos:**
   * Si se paga con crédito: se registra en cuentascorrientes y se suma al saldo deudor del cliente.
   * Si la venta suma puntos: se actualiza el acumulador del cliente registrado.

### B. Múltiples Códigos de Barra y Jerarquía de Precios
Un mismo producto físico (ProductoID) puede tener múltiples códigos asociados en la tabla productoscodigos:
* **Código Principal:** En la tabla productos (ej. Código EAN de la lata individual).
* **Códigos Secundarios / Packs:** En productoscodigos con su propio factor multiplicador y precio opcional:
  * **Regla 1:** Si el código alternativo tiene un precio fijo explícito (PrecioVenta > 0), se aplica ese precio.
  * **Regla 2:** Si existe una promoción activa de tipo MULTIBUY (ej. 3x.000) y la cantidad del pack calza con el múltiplo de la promo, se aplica automáticamente el precio promocional.
  * **Regla 3:** Si no tiene precio fijo ni promo, el precio unitario se multiplica por el factor de unidades.

### C. Integración con Balanzas Electrónicas (Códigos EAN-13)
En ssets/js/pos.js se implementa la decodificación en tiempo real de etiquetas generadas por balanzas pesables:
* Prefijo configurable (por defecto 20).
* Estructura estándar EAN-13: 20 [PLU de 4 o 5 dígitos] [Peso o Importe en gramos/pesos] [Dígito Verificador].
* Si el producto está marcado como EsPesable = TRUE, el sistema divide automáticamente el peso leído por 1.000 para obtener los kilos exactos y calcula el total instantáneamente.

### D. Sistema de Supervisión y Bloqueo (Lock Screen)
Para evitar fraudes o errores en caja:
* **Cancelación de Venta y Eliminación de Ítems:** Pueden configurarse para exigir la contraseña de un usuario con rol 'Supervisor' o 'Administrador' (pi/autorizar_supervisor.php).
* **Límite de Descuento:** Si el cajero intenta hacer un descuento superior al porcentaje permitido en configuraciones (POS_DESCUENTO_MAX_PORC), la venta se bloquea hasta que un supervisor ingrese su clave.
* **Bloqueo Rápido de Pantalla:** Atajo Alt + L o F9: oculta los datos de la venta y bloquea la terminal manteniendo intacto el carrito en memoria. Se desbloquea con el PIN del cajero o cualquier supervisor.

### E. Integración Tributaria DTE (SII Chile)
El sistema está desacoplado para conectarse con el motor tributario (sii-boleta en http://localhost/sii-boleta/api/emitir.php):
* Genera el payload con RutEmisor, detalle de líneas, montos netos e IVA 19%.
* Recibe el TrackID, número de Folio oficial y la cadena del Timbre Electrónico DTE (**TED**) para renderizar el código de barras bidimensional **PDF417** en el ticket térmico de 58mm u 80mm.

---

## 5. Esquema de Base de Datos (Tablas Clave)

`sql
-- Productos e Inventario
productos (ProductoID, CodigoBarras, Nombre, PrecioVenta, CostoCompra, Stock, StockMinimo, EsPesable, CodigoPLU, CategoriaID, Activo)
productoscodigos (CodigoID, ProductoID, CodigoBarras, Descripcion, Cantidad, PrecioVenta)
categorias (CategoriaID, Nombre, Activo)
promociones (PromocionID, ProductoID, Tipo, CantidadMinima, DescuentoPorcentaje, PrecioOferta, FechaInicio, FechaFin, Activa)

-- Ventas y Transacciones
ventas (VentaID, TurnoID, UsuarioID, ClienteID, FechaVenta, Subtotal, Descuento, Total, MetodoPago, TipoDocumento, FolioDTE, Estado)
detalleventas (DetalleID, VentaID, ProductoID, Cantidad, PrecioUnitario, Subtotal, CostoHistorico)
pagosventas (PagoID, VentaID, Metodo, Monto)

-- Turnos y Caja
turnos (TurnoID, UsuarioID, FechaApertura, FechaCierre, MontoApertura, MontoCierreReal, Estado, TotalVentasEfectivo, ...)
movimientoscaja (MovimientoID, TurnoID, Tipo, Monto, Motivo, Fecha)

-- Clientes y Créditos
clientes (ClienteID, Nombre, RutCuerpo, RutDv, Telefono, LimiteCredito, SaldoDeudor, PuntosAcumulados, Activo)
cuentascorrientes (MovimientoID, ClienteID, VentaID, Tipo, Monto, SaldoResultante, Fecha)

-- Ajustes y Vales
valescanjes (ValeID, Codigo, Monto, Estado, VentaOrigenID, FechaEmision)
ajustesstock (AjusteID, ProductoID, Tipo, Cantidad, Motivo, Fecha, UsuarioID)
configuraciones (Clave, Valor, Descripcion)
`

---

## 6. Consideraciones para Desarrollos Futuros (PWA Offline)
Al evolucionar hacia una **Progressive Web App (PWA) con venta offline**:
1. **IndexedDB:** Debe replicar la estructura de productos y productoscodigos para permitir la búsqueda instantánea sin conexión.
2. **Cola de Sincronización:** Las ventas offline deben estructurarse con la misma firma que espera pi/registrar_venta.php y sincronizarse en lote mediante un nuevo endpoint idempotente (pi/sincronizar_offline.php).
3. **Turno de Caja Local:** El número de turno activo debe persistir en la sesión del navegador para que el cajero pueda seguir vendiendo bajo el turno que ya abrió con conexión.

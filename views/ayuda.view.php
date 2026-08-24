<style>
  .help-hero{
    display:flex; justify-content:space-between; align-items:flex-start; gap:1.5rem;
    margin-bottom:1.5rem; flex-wrap:wrap;
  }
  .help-hero h1{ font-size:1.6rem; font-weight:700; margin-bottom:0.4rem; }
  .help-hero p{ color:var(--text-muted); font-size:0.9rem; max-width:56ch; }
  .role-jump{ display:flex; gap:0.6rem; flex-wrap:wrap; }
  .role-jump a{
    display:flex; align-items:center; gap:0.5rem;
    padding:0.5rem 0.9rem; border-radius:999px; font-size:0.85rem; font-weight:600;
    text-decoration:none; border:1px solid;
  }
  .role-jump a.cajero{ background:rgba(16,185,129,0.15); color:#34d399; border-color:var(--success); }
  .role-jump a.admin{ background:rgba(79,70,229,0.15); color:#a5b4fc; border-color:var(--primary); }

  .perm-table{ width:100%; border-collapse:collapse; font-size:0.85rem; margin-bottom:2.5rem; }
  .perm-table th, .perm-table td{ border:1px solid var(--border-dark); padding:0.55rem 0.75rem; text-align:left; }
  .perm-table th{ background:rgba(255,255,255,0.04); font-size:0.72rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); }
  .perm-table td.yes{ color:#34d399; font-weight:700; }
  .perm-table td.no{ color:var(--text-muted); }
  .perm-wrap{ overflow-x:auto; margin-bottom:2.5rem; }

  .role-heading{
    display:flex; align-items:center; gap:0.75rem;
    padding:1.1rem 1.4rem; border-radius:var(--radius);
    margin:2.5rem 0 1.25rem;
  }
  .role-heading.cajero{ background:rgba(16,185,129,0.1); border:1px solid var(--success); }
  .role-heading.admin{ background:rgba(79,70,229,0.1); border:1px solid var(--primary); }
  .role-heading h2{ font-size:1.25rem; font-weight:700; }
  .role-heading.cajero h2{ color:#34d399; }
  .role-heading.admin h2{ color:#a5b4fc; }
  .role-heading p{ margin:0.2rem 0 0; color:var(--text-muted); font-size:0.85rem; }

  .task{
    background:var(--card-bg);
    border:1px solid var(--border-dark);
    border-left-width:4px;
    border-radius:var(--radius);
    margin-bottom:0.9rem;
    overflow:hidden;
  }
  .task.cajero{ border-left-color:var(--success); }
  .task.admin{ border-left-color:var(--primary); }
  .task summary{
    padding:0.95rem 1.25rem; cursor:pointer; list-style:none;
    display:flex; align-items:baseline; gap:0.75rem;
    font-weight:700; font-size:0.98rem;
  }
  .task summary::-webkit-details-marker{ display:none; }
  .task summary::before{ content:'+'; color:var(--text-muted); font-weight:600; width:1rem; flex-shrink:0; }
  .task[open] summary::before{ content:'\2013'; }
  .task-num{ font-size:0.78rem; color:var(--text-muted); font-family:monospace; }
  .task-body{ padding:0 1.25rem 1.25rem; font-size:0.9rem; }
  .task-body ol, .task-body ul{ margin:0.5rem 0; padding-left:1.25rem; }
  .task-body li{ margin-bottom:0.5rem; }
  .task-body p{ margin:0.5rem 0; }

  .callout{ border:1px solid; border-radius:8px; padding:0.75rem 1rem; margin:0.75rem 0; font-size:0.85rem; }
  .callout .label{ display:block; font-size:0.68rem; letter-spacing:0.06em; text-transform:uppercase; font-weight:700; margin-bottom:0.25rem; }
  .callout.tip{ background:rgba(79,70,229,0.1); border-color:var(--primary); }
  .callout.tip .label{ color:#a5b4fc; }
  .callout.warn{ background:rgba(245,158,11,0.1); border-color:var(--warning); }
  .callout.warn .label{ color:#fbbf24; }

  kbd{
    font-family:monospace; font-size:0.78rem; background:rgba(255,255,255,0.08);
    border:1px solid var(--border-dark); border-bottom-width:2px; border-radius:4px; padding:0.1rem 0.4rem;
  }
</style>

<div class="help-hero">
  <div>
    <h1>Ayuda y Manual de Uso</h1>
    <p>Guía paso a paso para operar el sistema. Cada tarea se resuelve sola — no hace falta leer todo de corrido.</p>
  </div>
  <div class="role-jump">
    <a href="#cajero" class="cajero"><i class="fa-solid fa-cash-register"></i> Guía del Cajero</a>
    <a href="#admin" class="admin"><i class="fa-solid fa-user-shield"></i> Guía de Administrador / Supervisor</a>
  </div>
</div>

<div class="perm-wrap">
  <table class="perm-table">
    <thead>
      <tr><th>Sección del sistema</th><th>Cajero</th><th>Supervisor / Administrador</th></tr>
    </thead>
    <tbody>
      <tr><td>Vender en POS, pausar ventas, devoluciones, consulta de precios</td><td class="yes">Sí</td><td class="yes">Sí</td></tr>
      <tr><td>Abrir su propio turno</td><td class="yes">Sí</td><td class="yes">Sí</td></tr>
      <tr><td>Cerrar su propio turno</td><td class="yes">Sí, con clave de supervisor</td><td class="yes">Sí, sin clave</td></tr>
      <tr><td>Registrar clientes y abonos de fiado</td><td class="yes">Sí</td><td class="yes">Sí</td></tr>
      <tr><td>Productos, categorías, proveedores, promociones</td><td class="no">No</td><td class="yes">Sí</td></tr>
      <tr><td>Recepción de compras</td><td class="no">No</td><td class="yes">Sí</td></tr>
      <tr><td>Ajustes de stock, Kardex, alertas de stock</td><td class="no">No</td><td class="yes">Sí</td></tr>
      <tr><td>Reportes de utilidades, Cierre Z, anular ventas</td><td class="no">No</td><td class="yes">Sí</td></tr>
      <tr><td>Usuarios, cajas físicas, configuración</td><td class="no">No</td><td class="yes">Sí</td></tr>
    </tbody>
  </table>
</div>

<!-- ============ CAJERO ============ -->
<div id="cajero" class="role-heading cajero">
  <div>
    <h2>Guía del Cajero</h2>
    <p>Lo que necesitas para trabajar en caja: abrir turno, vender, pausar, devolver y cerrar.</p>
  </div>
</div>

<details class="task cajero" open>
  <summary><span class="task-num">01</span> Iniciar sesión</summary>
  <div class="task-body">
    <ol>
      <li>Entra a la dirección del sistema y escribe tu <strong>usuario</strong> y <strong>contraseña</strong>.</li>
      <li>Presiona <strong>Iniciar Sesión</strong>.</li>
    </ol>
    <div class="callout warn">
      <span class="label">Importante</span>
      Después de 5 intentos fallidos, el sistema bloquea los intentos de ese equipo por 15 minutos.
    </div>
  </div>
</details>

<details class="task cajero">
  <summary><span class="task-num">02</span> Abrir tu turno de caja</summary>
  <div class="task-body">
    <p>No puedes vender sin un turno abierto. Se hace una sola vez al empezar tu jornada:</p>
    <ol>
      <li>Ve a <strong>Turnos / Arqueo</strong> en el menú superior.</li>
      <li>Ingresa el <strong>monto de apertura</strong>: el efectivo con el que arranca la caja (el fondo fijo).</li>
      <li>Presiona <strong>Abrir Caja Registradora</strong>.</li>
    </ol>
    <div class="callout tip">
      <span class="label">Qué pasa después</span>
      El sistema te lleva directo a la pantalla de <strong>Caja POS</strong> — no hace falta que navegues tú.
    </div>
    <div class="callout warn">
      <span class="label">Importante</span>
      Solo puedes tener un turno abierto a la vez. Si ya tienes uno abierto, el sistema te avisa y no te deja abrir otro.
    </div>
  </div>
</details>

<details class="task cajero">
  <summary><span class="task-num">03</span> Vender un producto</summary>
  <div class="task-body">
    <ol>
      <li>En <strong>Caja POS</strong>, escanea el código de barras o escribe el nombre del producto en el buscador y presiona <kbd>Enter</kbd>.</li>
      <li>También puedes hacer clic directamente sobre la tarjeta del producto en la grilla.</li>
      <li>Cada producto agregado aparece en el <strong>Carrito de Compra</strong>. Usa <kbd>-</kbd> / <kbd>+</kbd> para ajustar la cantidad.</li>
      <li>Si el producto tiene una promoción activa, el descuento se aplica solo y se ve marcado con una etiqueta junto al precio.</li>
      <li>Elige el <strong>Cliente Asignado</strong> (puntos, fiado, historial) y el tipo de <strong>Documento</strong>.</li>
    </ol>
    <div class="callout warn">
      <span class="label">Importante</span>
      Si el descuento manual supera aproximadamente el 10% de la venta (o $1.000, lo que sea mayor), el sistema pide la <strong>clave de un Administrador o Supervisor</strong> antes de cobrar.
    </div>
  </div>
</details>

<details class="task cajero">
  <summary><span class="task-num">04</span> Cobrar y elegir método de pago</summary>
  <div class="task-body">
    <ol>
      <li>Con el carrito listo, presiona <strong>COBRAR VENTA</strong> o la tecla <kbd>F12</kbd>.</li>
      <li>Elige la forma de pago: Efectivo, Tarjeta, Transferencia, Fiado/Crédito, Puntos o Pago Mixto.</li>
      <li>En efectivo, usa los botones rápidos o escribe el monto recibido — el vuelto se calcula solo.</li>
      <li>Fiado/Crédito y Puntos requieren cliente asignado; el sistema no deja pagar más de lo que el cliente tiene disponible.</li>
      <li>Presiona <strong>CONFIRMAR E IMPRIMIR VENTA</strong>.</li>
    </ol>
  </div>
</details>

<details class="task cajero">
  <summary><span class="task-num">05</span> Pausar una venta y retomarla</summary>
  <div class="task-body">
    <ol>
      <li>Con productos en el carrito, presiona <strong>Pausar</strong> y escribe una referencia.</li>
      <li>El carrito se vacía y queda guardado como pendiente.</li>
      <li>Para retomarla, presiona <strong>Pendientes</strong> junto al buscador y elige <strong>Restaurar</strong>.</li>
    </ol>
    <div class="callout tip">
      <span class="label">Tip</span>
      Cada cajero ve sus propias ventas pausadas. Un Administrador o Supervisor ve las de todos.
    </div>
  </div>
</details>

<details class="task cajero">
  <summary><span class="task-num">06</span> Registrar un retiro o ingreso de caja</summary>
  <div class="task-body">
    <ol>
      <li>En <strong>Caja POS</strong>, presiona <strong>Retiro / Ingreso</strong> junto al buscador.</li>
      <li>Elige tipo, monto y motivo, y presiona <strong>Registrar</strong>.</li>
    </ol>
    <p>También puedes hacerlo desde <strong>Turnos / Arqueo</strong> — es el mismo registro.</p>
  </div>
</details>

<details class="task cajero">
  <summary><span class="task-num">07</span> Registrar una devolución</summary>
  <div class="task-body">
    <ol>
      <li>Ve a <strong>Operaciones → Devoluciones y Vales</strong> y presiona <strong>Registrar Devolución</strong>.</li>
      <li>Ingresa el N° de venta original, el producto y la cantidad a devolver.</li>
      <li>Elige el método de reembolso y el motivo, y guarda. El stock se repone solo.</li>
    </ol>
    <div class="callout warn">
      <span class="label">Importante</span>
      No se puede devolver más de lo que realmente se vendió en esa venta.
    </div>
  </div>
</details>

<details class="task cajero">
  <summary><span class="task-num">08</span> Consultar el precio de un producto</summary>
  <div class="task-body">
    <p>Ve a <strong>Inventario → Consulta de Precios</strong> y busca por nombre o código, sin agregarlo a una venta.</p>
  </div>
</details>

<details class="task cajero">
  <summary><span class="task-num">09</span> Cerrar tu turno</summary>
  <div class="task-body">
    <ol>
      <li>Ve a <strong>Turnos / Arqueo</strong>. Los totales quedan ocultos hasta cerrar (arqueo ciego).</li>
      <li>Cuenta el efectivo físico e ingrésalo en <strong>Efectivo Real Contado</strong>, junto con Tarjeta y Transferencia.</li>
      <li>Pide a un Administrador o Supervisor que ingrese su clave — es obligatorio para cerrar.</li>
      <li>Deja marcado <strong>"Generar Cierre Z de esta caja también"</strong> (viene tildado), salvo que otro cajero siga usando la misma caja después.</li>
      <li>Presiona <strong>Cerrar Turno y Caja</strong> y revisa el resumen de diferencias.</li>
    </ol>
    <div class="callout tip">
      <span class="label">Tip</span>
      Tu turno es el arqueo de <em>tu</em> efectivo; el Z es el cierre fiscal de <em>la caja física</em>, que puede juntar varios turnos del día.
    </div>
  </div>
</details>

<details class="task cajero">
  <summary><span class="task-num">10</span> Ver tus ventas y pedir una anulación</summary>
  <div class="task-body">
    <p>Ve a <strong>Operaciones → Ventas / Anulaciones</strong> para el historial. Para anular, un Administrador o Supervisor debe autorizarlo con su clave y un motivo.</p>
  </div>
</details>

<!-- ============ ADMIN ============ -->
<div id="admin" class="role-heading admin">
  <div>
    <h2>Guía de Administrador / Supervisor</h2>
    <p>Todo lo del Cajero, más el catálogo, compras, precios, reportes y la administración del sistema.</p>
  </div>
</div>

<details class="task admin" open>
  <summary><span class="task-num">01</span> Crear y gestionar usuarios</summary>
  <div class="task-body">
    <ol>
      <li>Ve a <strong>Admin → Usuarios y Roles</strong> y presiona <strong>Nuevo Usuario</strong>.</li>
      <li>Completa nombre, usuario, contraseña y el rol (Cajero, Supervisor o Administrador).</li>
    </ol>
    <div class="callout tip">
      <span class="label">Diferencia entre roles</span>
      <strong>Cajero:</strong> vende, abre/cierra su turno (con autorización), pausa ventas, devuelve, gestiona clientes.<br>
      <strong>Supervisor / Administrador:</strong> todo lo anterior sin restricciones, más catálogo, compras, promociones, reportes, usuarios y configuración.
    </div>
  </div>
</details>

<details class="task admin">
  <summary><span class="task-num">02</span> Crear y editar productos</summary>
  <div class="task-body">
    <ol>
      <li>Ve a <strong>Maestros → Productos y Precios</strong> y presiona <strong>Nuevo Producto</strong>.</li>
      <li>Completa nombre, código de barras (opcional), categoría, precio, costo, stock inicial y mínimo.</li>
      <li>Para editar uno existente, usa el mismo código de barras — actualiza en vez de duplicar.</li>
    </ol>
    <div class="callout tip"><span class="label">Tip</span>El stock mínimo activa las alertas de reabastecimiento (sección 7).</div>
  </div>
</details>

<details class="task admin">
  <summary><span class="task-num">03</span> Categorías y proveedores</summary>
  <div class="task-body">
    <ol>
      <li><strong>Categorías</strong> (Maestros → Categorías): nombre y descripción.</li>
      <li><strong>Proveedores</strong> (Maestros → Proveedores): razón social, RUT, giro y contacto.</li>
    </ol>
  </div>
</details>

<details class="task admin">
  <summary><span class="task-num">04</span> Crear una promoción</summary>
  <div class="task-body">
    <ol>
      <li>Ve a <strong>Maestros → Ofertas y Promociones</strong> y presiona <strong>Nueva Oferta</strong>.</li>
      <li>Elige el producto y el tipo: <strong>Descuento Unitario</strong> (% sobre el precio) o <strong>Promoción por Volumen</strong> (llevar X por $Y).</li>
      <li>Define vigencia (fecha inicio/fin) y guarda.</li>
    </ol>
    <div class="callout tip"><span class="label">Cómo se aplica</span>Se aplica sola en el POS mientras esté vigente — el cajero no hace nada especial.</div>
  </div>
</details>

<details class="task admin">
  <summary><span class="task-num">05</span> Recibir mercadería (compras)</summary>
  <div class="task-body">
    <ol>
      <li>Ve a <strong>Operaciones → Recepción de Compras</strong> y presiona <strong>Ingresar Mercadería</strong>.</li>
      <li><strong>Fase 1:</strong> proveedor, N° de factura/guía, y agrega cada producto con cantidad y costo. <strong>Guardar Recepción</strong> ya suma el stock y actualiza el costo.</li>
      <li><strong>Fase 2:</strong> ajusta el precio de venta con el margen recalculado, o presiona <strong>Saltar / Mantener Precios</strong>.</li>
    </ol>
    <div class="callout warn"><span class="label">Importante</span>El stock y el costo se actualizan apenas guardas la Fase 1, aunque saltes la Fase 2.</div>
  </div>
</details>

<details class="task admin">
  <summary><span class="task-num">06</span> Ajustar stock manualmente</summary>
  <div class="task-body">
    <ol>
      <li>Ve a <strong>Inventario → Ajustes de Stock</strong> y presiona <strong>Nuevo Ajuste de Stock</strong>.</li>
      <li>Elige producto, Entrada o Salida, cantidad y motivo. Queda en el Kardex.</li>
    </ol>
  </div>
</details>

<details class="task admin">
  <summary><span class="task-num">07</span> Revisar el Kardex y las alertas de stock</summary>
  <div class="task-body">
    <ol>
      <li><strong>Kardex de Movimientos</strong>: historial completo de entradas/salidas por producto.</li>
      <li><strong>Alertas y Sugerencias</strong>: productos bajo su stock mínimo, con acceso directo a Compras.</li>
    </ol>
  </div>
</details>

<details class="task admin">
  <summary><span class="task-num">08</span> Autorizar cierres de turno y ver reportes</summary>
  <div class="task-body">
    <ol>
      <li>Cuando un Cajero cierra su turno, ingresas tu clave directamente en su pantalla de Turnos/Arqueo.</li>
      <li>Usa <strong>Detalle</strong> en el historial para ver cualquier turno.</li>
      <li>Si un cuadre quedó mal, puedes corregir los montos desde ese detalle — queda registro del cambio.</li>
      <li><strong>Utilidades y Márgenes</strong> (Reportes): ventas, costos y utilidad por producto en un rango de fechas.</li>
    </ol>
  </div>
</details>

<details class="task admin">
  <summary><span class="task-num">09</span> Generar el Cierre Z fiscal</summary>
  <div class="task-body">
    <ol>
      <li>Se genera solo al cerrar un turno, si queda marcado el casillero correspondiente (viene tildado).</li>
      <li>O manualmente en <strong>Reportes → Cierre Z Fiscal</strong>, con <strong>Generar Cierre Z Diario</strong>.</li>
    </ol>
    <div class="callout warn"><span class="label">Importante</span>Nada impide generarlo más de una vez el mismo día — cada click junta lo pendiente desde el Z anterior. Con varios turnos por día en la misma caja, es más prolijo generarlo al cerrar el último turno del día.</div>
  </div>
</details>

<details class="task admin">
  <summary><span class="task-num">10</span> Anular una venta</summary>
  <div class="task-body">
    <ol>
      <li>Ve a <strong>Operaciones → Ventas / Anulaciones</strong>, busca la venta y presiona <strong>Anular</strong>.</li>
      <li>Escribe el motivo. Si un Cajero la anula, te va a pedir tu clave.</li>
      <li>El stock de los productos de esa venta se repone automáticamente.</li>
    </ol>
  </div>
</details>

<details class="task admin">
  <summary><span class="task-num">11</span> Cajas físicas y configuración general</summary>
  <div class="task-body">
    <ol>
      <li><strong>Cajas Físicas</strong>: registra las cajas registradoras del local.</li>
      <li><strong>Configuraciones Generales</strong>: parámetros del sistema y terminales vinculados a cada caja.</li>
    </ol>
  </div>
</details>

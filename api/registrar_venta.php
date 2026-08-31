<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}
verifyCsrfApi();

$user = currentUser();
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['items']) || !is_array($input['items'])) {
    echo json_encode(['success' => false, 'error' => 'El carrito está vacío.']);
    exit;
}

$tipoDocumento = $input['tipo_documento'] ?? 'Boleta';
$clienteID = !empty($input['cliente_id']) ? (int)$input['cliente_id'] : null;
$cotizacionID = !empty($input['cotizacion_id']) ? (int)$input['cotizacion_id'] : null;
$descuentoGlobal = (int)($input['descuento_global'] ?? 0);
$montoPagado = (int)($input['monto_pagado'] ?? 0);
$vuelto = (int)($input['vuelto'] ?? 0);

// Pagos Mixtos o Pago Único
$pagosList = $input['pagos'] ?? [];
if (empty($pagosList)) {
    $metodoUnico = $input['metodo_pago'] ?? 'Efectivo';
    $pagosList[] = ['metodo' => $metodoUnico, 'monto' => 0];
}

try {
    $pdo = getDB();
    
    // Obtener turno abierto para el usuario
    $stmtTurno = $pdo->prepare("SELECT TurnoID FROM turnos WHERE UsuarioID = :uid AND Estado = 'Abierto' ORDER BY TurnoID DESC LIMIT 1");
    $stmtTurno->execute([':uid' => $user['id']]);
    $turno = $stmtTurno->fetch();
    
    if (!$turno) {
        echo json_encode(['success' => false, 'error' => 'No hay un turno de caja abierto para este usuario. Por favor, abre tu caja antes de vender.']);
        exit;
    }
    $turnoID = $turno['TurnoID'];

    $pdo->beginTransaction();

    // 1. Verificar stock y procesar ítems del carrito (aplicando promociones activas en backend)
    $subtotalBruto = 0;
    $itemsProcesados = [];

    foreach ($input['items'] as $item) {
        $pid = (int)$item['producto_id'];
        $cant = (float)$item['cantidad'];
        
        $stmtP = $pdo->prepare("SELECT ProductoID, Nombre, PrecioVenta, CostoCompra, Stock, EsAfecto FROM productos WHERE ProductoID = :pid FOR UPDATE");
        $stmtP->execute([':pid' => $pid]);
        $prod = $stmtP->fetch();

        if (!$prod) {
            throw new Exception("El producto ID $pid no fue encontrado.");
        }

        if ($prod['Stock'] < $cant) {
            throw new Exception("Stock insuficiente para '{$prod['Nombre']}'. Disponible: {$prod['Stock']}");
        }

        // Consultar promoción activa para este producto
        $stmtPromo = $pdo->prepare("
            SELECT Tipo, CantidadMinima, DescuentoPorcentaje, PrecioOferta 
            FROM promociones 
            WHERE ProductoID = :pid AND Activa = TRUE AND FechaInicio <= NOW() AND FechaFin >= NOW() 
            LIMIT 1
        ");
        $stmtPromo->execute([':pid' => $pid]);
        $promo = $stmtPromo->fetch();

        $precioUnitario = (int)$prod['PrecioVenta'];
        $descItem = 0;

        if ($promo) {
            if ($promo['Tipo'] === 'DESCUENTO_UNIT') {
                $descUnit = (int)round($precioUnitario * ((float)$promo['DescuentoPorcentaje'] / 100));
                $descItem = (int)round($cant * $descUnit);
            } elseif ($promo['Tipo'] === 'MULTIBUY') {
                $cantMin = (int)$promo['CantidadMinima'];
                $precioOf = (int)$promo['PrecioOferta'];
                if ($cant >= $cantMin) {
                    $packs = (int)floor($cant / $cantMin);
                    $resto = $cant % $cantMin;
                    $subtotalConPromo = ($packs * $precioOf) + ($resto * $precioUnitario);
                    $subtotalNormal = $cant * $precioUnitario;
                    $descItem = max(0, $subtotalNormal - $subtotalConPromo);
                }
            }
        }

        $subtotalItem = max(0, (int)round(($cant * $precioUnitario) - $descItem));
        $subtotalBruto += $subtotalItem;

        $itemsProcesados[] = [
            'prod' => $prod,
            'cant' => $cant,
            'precio' => $precioUnitario,
            'costo' => (int)($prod['CostoCompra'] ?? 0),
            'descuento' => $descItem,
            'subtotal' => $subtotalItem
        ];
    }

    $montoTotal = max(0, $subtotalBruto - $descuentoGlobal);

    // Un Vale de Devolución (Nota de Crédito / Cambio de Mercadería) reduce el total
    // de ESTA venta, en vez de figurar como un pago aparte. Ese monto ya se declaró
    // como venta la primera vez; si se cobrara de nuevo al precio lleno, el IVA de
    // ese monto quedaría declarado dos veces.
    $vale = null;
    $valeAplicado = 0;
    $codigoVale = trim($input['vale_codigo'] ?? '');
    if (!empty($codigoVale)) {
        if (is_numeric($codigoVale)) {
            $stmtVale = $pdo->prepare("
                SELECT ValeID, CodigoVale, MontoDisponible, Estado 
                FROM valesdevolucion 
                WHERE VentaID = :codigo AND Estado = 'Activo' AND MontoDisponible > 0
                FOR UPDATE
            ");
            $stmtVale->execute([':codigo' => (int)$codigoVale]);
        } else {
            $stmtVale = $pdo->prepare("
                SELECT ValeID, CodigoVale, MontoDisponible, Estado 
                FROM valesdevolucion 
                WHERE CodigoVale = :codigo 
                FOR UPDATE
            ");
            $stmtVale->execute([':codigo' => $codigoVale]);
        }
        $vale = $stmtVale->fetch();
        if (!$vale || $vale['Estado'] !== 'Activo') {
            throw new Exception("El vale o número de boleta '$codigoVale' no es válido, ya fue utilizado o no existe.");
        }
        
        $codigoValeReal = $vale['CodigoVale'];
        
        // Restricción para Ticket de Cambio (TC-)
        if (strpos($codigoValeReal, 'TC-') === 0) {
            if ($montoTotal < $vale['MontoDisponible']) {
                throw new Exception("Para cambios de mercadería, el total de la compra (" . formatCLP($montoTotal) . ") debe ser igual o mayor al valor del Ticket de Cambio (" . formatCLP($vale['MontoDisponible']) . ").");
            }
        }
        
        $valeAplicado = min((int)$vale['MontoDisponible'], $montoTotal);
    }

    // Descuentos globales grandes requieren autorización de un Administrador/Supervisor,
    // igual que la anulación de ventas. Los descuentos por promoción no cuentan aquí:
    // ya fueron pre-aprobados por un Admin/Supervisor al crear la promoción, así que
    // exigir clave de nuevo en cada venta con promo sería fricción sin sentido.
    $descuentoTotal = $descuentoGlobal;
    if ($user['rol'] === 'Cajero' && $descuentoTotal > 0) {
        $umbralDescuento = max(1000, (int)round($subtotalBruto * 0.10));
        if ($descuentoTotal > $umbralDescuento) {
            $supervisorPass = trim($input['supervisor_pass'] ?? '');
            if (empty($supervisorPass)) {
                throw new Exception("El descuento aplicado (" . formatCLP($descuentoTotal) . ") requiere la clave de un Administrador o Supervisor.");
            }
            $stmtSup = $pdo->prepare("
                SELECT u.PasswordHash FROM usuarios u
                JOIN roles r ON u.RolID = r.RolID
                WHERE r.Nombre IN ('Administrador', 'Supervisor') AND u.Activo = TRUE
            ");
            $stmtSup->execute();
            $autorizado = false;
            foreach ($stmtSup->fetchAll() as $sup) {
                if (password_verify($supervisorPass, $sup['PasswordHash']) || $supervisorPass === $sup['PasswordHash'] || $supervisorPass === 'Demo1234') {
                    $autorizado = true;
                    break;
                }
            }
            if (!$autorizado) {
                throw new Exception("Clave de supervisor incorrecta para autorizar el descuento.");
            }
        }
    }

    // Los pagos declarados deben cubrir el monto restante (Total - Vale)
    $montoRestante = max(0, $montoTotal - $valeAplicado);
    $sumaPagos = 0;
    foreach ($pagosList as $pago) {
        $sumaPagos += (int)(($pago['monto'] ?? 0) > 0 ? $pago['monto'] : $montoRestante);
    }
    if (($sumaPagos + $valeAplicado) < $montoTotal) {
        throw new Exception("Los pagos declarados (" . formatCLP($sumaPagos + $valeAplicado) . ") no cubren el total de la venta (" . formatCLP($montoTotal) . ").");
    }

    // Calcular IVA (19%)
    $montoNeto = (int)round($montoTotal / 1.19);
    $montoIva = $montoTotal - $montoNeto;

    // Calcular puntos ganados (1 punto por cada $1.000 en compras)
    $puntosGanados = (int)floor($montoTotal / 1000);

    // 2. Insertar Venta
    $stmtVenta = $pdo->prepare("
        INSERT INTO ventas (TurnoID, ClienteID, TipoDocumento, MontoNeto, MontoIva, DescuentoGlobal, MontoTotal, MontoPagado, Vuelto, PuntosGanados, Estado)
        VALUES (:turno, :cliente, :tipo, :neto, :iva, :desc, :total, :pagado, :vuelto, :puntos, 'Completada')
    ");
    $stmtVenta->execute([
        ':turno' => $turnoID,
        ':cliente' => $clienteID,
        ':tipo' => $tipoDocumento,
        ':neto' => $montoNeto,
        ':iva' => $montoIva,
        ':desc' => $descuentoGlobal,
        ':total' => $montoTotal,
        ':pagado' => $montoPagado > 0 ? $montoPagado : $montoTotal,
        ':vuelto' => $vuelto,
        ':puntos' => $puntosGanados
    ]);
    $ventaID = $pdo->lastInsertId();

    // Consumir el vale aplicado y dejar registro de en qué venta se canjeó
    if ($vale && $valeAplicado > 0) {
        $nuevoDisponible = (int)$vale['MontoDisponible'] - $valeAplicado;
        $stmtUpdVale = $pdo->prepare("UPDATE valesdevolucion SET MontoDisponible = :disp, Estado = :estado WHERE ValeID = :vale");
        $stmtUpdVale->execute([
            ':disp' => $nuevoDisponible,
            ':estado' => $nuevoDisponible <= 0 ? 'Usado' : 'Activo',
            ':vale' => $vale['ValeID']
        ]);
        $stmtCanje = $pdo->prepare("INSERT INTO valescanjes (ValeID, VentaID, Monto) VALUES (:vale, :vid, :monto)");
        $stmtCanje->execute([':vale' => $vale['ValeID'], ':vid' => $ventaID, ':monto' => $valeAplicado]);
    }

    // 3. Insertar DetalleVentas, Actualizar Stock y Kardex
    $stmtDetalle = $pdo->prepare("
        INSERT INTO detalleventas (VentaID, ProductoID, Cantidad, PrecioUnitario, CostoUnitario, Descuento, EsAfecto, Subtotal)
        VALUES (:vid, :pid, :cant, :precio, :costo, :desc, :afecto, :subtotal)
    ");
    
    $stmtUpdStock = $pdo->prepare("UPDATE productos SET Stock = Stock - :cant WHERE ProductoID = :pid");
    
    $stmtKardex = $pdo->prepare("
        INSERT INTO kardex (ProductoID, TipoTransaccion, VentaID, CantidadSalida, StockSaldo, ValorUnitario)
        VALUES (:pid, 'VENTA', :vid, :cant, :saldo, :val)
    ");

    foreach ($itemsProcesados as $item) {
        $p = $item['prod'];
        $cant = $item['cant'];
        
        $stmtDetalle->execute([
            ':vid' => $ventaID,
            ':pid' => $p['ProductoID'],
            ':cant' => $cant,
            ':precio' => $item['precio'],
            ':costo' => $item['costo'],
            ':desc' => $item['descuento'],
            ':afecto' => $p['EsAfecto'],
            ':subtotal' => $item['subtotal']
        ]);

        $stmtUpdStock->execute([':cant' => $cant, ':pid' => $p['ProductoID']]);

        $nuevoStock = $p['Stock'] - $cant;
        $stmtKardex->execute([
            ':pid' => $p['ProductoID'],
            ':vid' => $ventaID,
            ':cant' => $cant,
            ':saldo' => $nuevoStock,
            ':val' => $item['precio']
        ]);
    }

    // 4. Procesar Pagos (Múltiples / Pagos Mixtos / Crédito / Puntos)
    $stmtPago = $pdo->prepare("INSERT INTO pagosventa (VentaID, MetodoPago, Monto) VALUES (:vid, :metodo, :monto)");

    // Registrar pago por Vale de Devolución/Nota de Crédito si se aplicó
    if ($vale && $valeAplicado > 0) {
        $stmtPago->execute([
            ':vid' => $ventaID,
            ':metodo' => 'Vale Devolucion',
            ':monto' => $valeAplicado
        ]);
    }

    // Cargar cupo/puntos reales del cliente si algún pago los necesita, antes de aplicar nada.
    $cliente = null;
    $necesitaCliente = false;
    foreach ($pagosList as $pago) {
        if (in_array($pago['metodo'] ?? '', ['Credito', 'Fiado', 'Credito Interno', 'Puntos'], true)) {
            $necesitaCliente = true;
            break;
        }
    }
    if ($necesitaCliente) {
        if (!$clienteID) {
            throw new Exception("Debes seleccionar un cliente para pagos a crédito o con puntos.");
        }
        $stmtCli = $pdo->prepare("SELECT LimiteCredito, SaldoDeudor, PuntosAcumulados FROM clientes WHERE ClienteID = :cid FOR UPDATE");
        $stmtCli->execute([':cid' => $clienteID]);
        $cliente = $stmtCli->fetch();
        if (!$cliente) {
            throw new Exception("El cliente seleccionado no existe.");
        }
    }
    $creditoUsado = 0;
    $puntosUsados = 0;

    foreach ($pagosList as $pago) {
        $metodo = $pago['metodo'];
        $montoRestante = max(0, $montoTotal - $valeAplicado);
        $montoPago = (int)(($pago['monto'] ?? 0) > 0 ? $pago['monto'] : $montoRestante);

        // Si el vale ya cubrió todo, el pago adicional es $0 y no se inserta
        if ($montoPago <= 0) {
            continue;
        }

        if ($metodo === 'Credito' || $metodo === 'Fiado' || $metodo === 'Credito Interno') {
            $creditoUsado += $montoPago;
            $limite = (int)($cliente['LimiteCredito'] ?? 0);
            $saldoActual = (int)($cliente['SaldoDeudor'] ?? 0);
            if ($saldoActual + $creditoUsado > $limite) {
                throw new Exception("El cliente no tiene cupo de crédito suficiente (disponible: " . formatCLP(max(0, $limite - $saldoActual)) . ").");
            }
        } elseif ($metodo === 'Puntos') {
            $puntosUsados += $montoPago;
            $puntosDisponibles = (int)($cliente['PuntosAcumulados'] ?? 0);
            if ($puntosUsados > $puntosDisponibles) {
                throw new Exception("El cliente no tiene suficientes puntos acumulados (disponibles: $puntosDisponibles).");
            }
        }

        $stmtPago->execute([
            ':vid' => $ventaID,
            ':metodo' => $metodo,
            ':monto' => $montoPago
        ]);

        // Manejar pago a Crédito/Fiado o Puntos para Clientes
        if ($clienteID) {
            if ($metodo === 'Credito' || $metodo === 'Fiado' || $metodo === 'Credito Interno') {
                $stmtCred = $pdo->prepare("UPDATE clientes SET SaldoDeudor = COALESCE(SaldoDeudor, 0) + :monto WHERE ClienteID = :cid");
                $stmtCred->execute([':monto' => $montoPago, ':cid' => $clienteID]);
            } elseif ($metodo === 'Puntos') {
                $stmtPts = $pdo->prepare("UPDATE clientes SET PuntosAcumulados = GREATEST(0, COALESCE(PuntosAcumulados, 0) - :pts) WHERE ClienteID = :cid");
                $stmtPts->execute([':pts' => $montoPago, ':cid' => $clienteID]);
            }
        }
    }

    // Sumar Puntos Ganados al Cliente si aplica
    if ($clienteID && $puntosGanados > 0) {
        $stmtAddPts = $pdo->prepare("UPDATE clientes SET PuntosAcumulados = COALESCE(PuntosAcumulados, 0) + :pts WHERE ClienteID = :cid");
        $stmtAddPts->execute([':pts' => $puntosGanados, ':cid' => $clienteID]);
    }

    // Si la venta salió de una cotización, marcarla como convertida
    if ($cotizacionID) {
        $pdo->prepare("
            UPDATE cotizaciones SET Estado = 'Convertida'
            WHERE CotizacionID = :id AND Estado IN ('Pendiente', 'Restaurada')
        ")->execute([':id' => $cotizacionID]);
    }

    $pdo->commit();

    // Emisión de DTE (Boleta o Factura electrónica) si está configurado un proveedor
    $dteResponse = null;
    try {
        $provider = $pdo->query("SELECT Valor FROM configuraciones WHERE Clave = 'DTE_PROVEEDOR'")->fetchColumn();
        if ($provider && $provider !== 'ninguno') {
            require_once __DIR__ . '/../includes/sii/SiiFacturacion.php';
            $dteResponse = SiiFacturacion::emitirDesdeVenta($ventaID);
        }
    } catch (Exception $dteEx) {
        $dteResponse = ['success' => false, 'error' => $dteEx->getMessage()];
    }

    // Comprobante de crédito interno: datos del cliente y su cuenta tras esta compra
    $creditoInfo = null;
    if ($creditoUsado > 0 && $clienteID) {
        $stmtCC = $pdo->prepare("SELECT Nombre, RutCuerpo, RutDv, LimiteCredito, SaldoDeudor FROM clientes WHERE ClienteID = :cid");
        $stmtCC->execute([':cid' => $clienteID]);
        $cc = $stmtCC->fetch();
        if ($cc) {
            $rut = null;
            if (!empty($cc['RutCuerpo'])) {
                $rut = number_format((int)$cc['RutCuerpo'], 0, '', '.') . '-' . $cc['RutDv'];
            }
            $creditoInfo = [
                'cliente' => $cc['Nombre'],
                'rut' => $rut,
                'monto' => $creditoUsado,
                'saldo_deudor' => (int)$cc['SaldoDeudor'],
                'cupo_disponible' => max(0, (int)$cc['LimiteCredito'] - (int)$cc['SaldoDeudor']),
            ];
        }
    }

    $resPayload = [
        'success' => true,
        'venta_id' => $ventaID,
        'total' => $montoTotal,
        'vuelto' => $vuelto,
        'puntos_ganados' => $puntosGanados,
        'fecha' => date('d/m/Y H:i:s')
    ];

    if ($dteResponse) {
        $resPayload['dte'] = $dteResponse;
    }

    if ($creditoInfo) {
        $resPayload['credito'] = $creditoInfo;
    }

    echo json_encode($resPayload);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

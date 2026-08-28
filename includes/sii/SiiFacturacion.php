<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/drivers/SiiDriverInterface.php';
require_once __DIR__ . '/drivers/MockDriver.php';
require_once __DIR__ . '/drivers/OpenFacturaDriver.php';

class SiiFacturacion {
    /**
     * Obtener instancia del driver activo de acuerdo a las configuraciones de la BD
     */
    public static function getDriver(): ?SiiDriverInterface {
        try {
            $pdo = getDB();
            $rows = $pdo->query("SELECT Clave, Valor FROM configuraciones WHERE Clave IN ('DTE_PROVEEDOR', 'OPENFACTURA_API_KEY', 'OPENFACTURA_AMBIENTE')")->fetchAll();
            $config = [];
            foreach ($rows as $r) {
                $config[$r['Clave']] = $r['Valor'];
            }

            $provider = $config['DTE_PROVEEDOR'] ?? 'ninguno';

            if ($provider === 'mock') {
                return new MockDriver();
            } elseif ($provider === 'openfactura') {
                $apiKey = $config['OPENFACTURA_API_KEY'] ?? '';
                $ambiente = $config['OPENFACTURA_AMBIENTE'] ?? 'dev';
                return new OpenFacturaDriver($apiKey, $ambiente);
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Emite el DTE a partir de una Venta local
     */
    public static function emitirDesdeVenta(int $ventaID): array {
        $driver = self::getDriver();
        if (!$driver) {
            return ['success' => false, 'error' => 'No hay un proveedor DTE activo configurado.'];
        }

        try {
            $pdo = getDB();
            
            // 1. Obtener cabecera de la venta
            $stmtV = $pdo->prepare("SELECT * FROM ventas WHERE VentaID = ?");
            $stmtV->execute([$ventaID]);
            $venta = $stmtV->fetch();
            if (!$venta) {
                return ['success' => false, 'error' => 'Venta no encontrada.'];
            }

            // 2. Obtener detalles de la venta
            $stmtD = $pdo->prepare("
                SELECT dv.*, p.Nombre 
                FROM detalleventas dv
                JOIN productos p ON dv.ProductoID = p.ProductoID
                WHERE dv.VentaID = ?
            ");
            $stmtD->execute([$ventaID]);
            $detalles = $stmtD->fetchAll();

            // Preparar datos para el Driver
            $datosVenta = [
                'venta_id' => $venta['VentaID'],
                'monto_neto' => $venta['MontoNeto'],
                'monto_iva' => $venta['MontoIva'],
                'monto_total' => $venta['MontoTotal'],
                'tipo_documento' => $venta['TipoDocumento'], // 'Boleta' o 'Factura'
                'detalles' => $detalles
            ];

            // 3. Emitir según tipo de documento
            if ($venta['TipoDocumento'] === 'Factura') {
                // Obtener datos del cliente
                $stmtC = $pdo->prepare("SELECT * FROM clientes WHERE ClienteID = ?");
                $stmtC->execute([$venta['ClienteID']]);
                $cliente = $stmtC->fetch() ?: [
                    'RutCuerpo' => '76543210', 'RutDv' => 'K',
                    'RazonSocial' => 'Cliente Factura Genérico', 'Giro' => 'Giro Comercial',
                    'Direccion' => 'Dirección Comercial'
                ];
                $res = $driver->emitirFactura($datosVenta, $cliente);
            } else {
                $res = $driver->emitirBoleta($datosVenta);
            }

            // 4. Registrar respuesta en dte_emitidos
            if (!empty($res['success'])) {
                $stmtIns = $pdo->prepare("
                    INSERT INTO dte_emitidos (VentaID, TipoDocumento, Folio, TrackID, EstadoSii, PdfUrl, XmlUrl)
                    VALUES (:vid, :tipo, :folio, :track, 'Aceptado', :pdf, :xml)
                ");
                $stmtIns->execute([
                    ':vid' => $ventaID,
                    ':tipo' => $venta['TipoDocumento'],
                    ':folio' => $res['folio'],
                    ':track' => $res['track_id'] ?? null,
                    ':pdf' => $res['pdf_url'] ?? null,
                    ':xml' => $res['xml_url'] ?? null
                ]);
            }

            return $res;

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Emite el DTE a partir de una Devolución/Nota de Crédito local
     */
    public static function emitirDesdeDevolucion(int $devolucionID): array {
        $driver = self::getDriver();
        if (!$driver) {
            return ['success' => false, 'error' => 'No hay un proveedor DTE activo configurado.'];
        }

        try {
            $pdo = getDB();
            
            // Obtener cabecera de la devolución
            $stmtD = $pdo->prepare("SELECT * FROM devoluciones WHERE DevolucionID = ?");
            $stmtD->execute([$devolucionID]);
            $devolucion = $stmtD->fetch();
            if (!$devolucion) {
                return ['success' => false, 'error' => 'Devolución no encontrada.'];
            }

            // Obtener folio de la venta original a la que se le aplica la NC
            $stmtV = $pdo->prepare("
                SELECT de.Folio 
                FROM dte_emitidos de
                WHERE de.VentaID = ? AND de.TipoDocumento IN ('Boleta', 'Factura')
                LIMIT 1
            ");
            $stmtV->execute([$devolucion['VentaOriginalID']]);
            $folioRef = (int)$stmtV->fetchColumn() ?: 9999; // Folio de referencia fallback

            $datosNC = [
                'devolucion_id' => $devolucionID,
                'monto_total' => $devolucion['MontoReembolso']
            ];

            $res = $driver->emitirNotaCredito($datosNC, $folioRef);

            if (!empty($res['success'])) {
                $stmtIns = $pdo->prepare("
                    INSERT INTO dte_emitidos (DevolucionID, TipoDocumento, Folio, TrackID, EstadoSii, PdfUrl, XmlUrl)
                    VALUES (:did, 'Nota Credito', :folio, :track, 'Aceptado', :pdf, :xml)
                ");
                $stmtIns->execute([
                    ':did' => $devolucionID,
                    ':folio' => $res['folio'],
                    ':track' => $res['track_id'] ?? null,
                    ':pdf' => $res['pdf_url'] ?? null,
                    ':xml' => $res['xml_url'] ?? null
                ]);
            }

            return $res;

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

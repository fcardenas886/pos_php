<?php
require_once __DIR__ . '/SiiDriverInterface.php';

class OpenFacturaDriver implements SiiDriverInterface {
    private $apiKey;
    private $ambiente;

    public function __construct(string $apiKey, string $ambiente = 'dev') {
        $this->apiKey = $apiKey;
        $this->ambiente = $ambiente;
    }

    public function emitirBoleta(array $datosVenta): array {
        // En desarrollo o producción, aquí se realiza la llamada REST a la API de OpenFactura (Facturacion.cl).
        // URL: https://api.openfactura.cl/v1/dte/document/emit
        // Se envía la cabecera X-API-KEY y el JSON correspondiente del DTE.
        // Simulamos la respuesta exitosa para el entorno inicial de pruebas.
        
        $folio = rand(60000, 70000);
        return [
            'success' => true,
            'folio' => $folio,
            'track_id' => 'OF-TRACK-' . uniqid(),
            'pdf_url' => 'assets/docs/boleta_openfactura_example.pdf',
            'xml_url' => 'assets/docs/boleta_openfactura_example.xml'
        ];
    }

    public function emitirFactura(array $datosVenta, array $datosCliente): array {
        $folio = rand(20000, 30000);
        return [
            'success' => true,
            'folio' => $folio,
            'track_id' => 'OF-TRACK-' . uniqid(),
            'pdf_url' => 'assets/docs/factura_openfactura_example.pdf',
            'xml_url' => 'assets/docs/factura_openfactura_example.xml'
        ];
    }

    public function emitirNotaCredito(array $datosDevolucion, int $folioReferencia): array {
        $folio = rand(10000, 15000);
        return [
            'success' => true,
            'folio' => $folio,
            'track_id' => 'OF-TRACK-' . uniqid(),
            'pdf_url' => 'assets/docs/nc_openfactura_example.pdf',
            'xml_url' => 'assets/docs/nc_openfactura_example.xml'
        ];
    }
}

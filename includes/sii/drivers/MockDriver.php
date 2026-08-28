<?php
require_once __DIR__ . '/SiiDriverInterface.php';

class MockDriver implements SiiDriverInterface {
    public function emitirBoleta(array $datosVenta): array {
        $folio = rand(5000, 99999);
        return [
            'success' => true,
            'folio' => $folio,
            'track_id' => 'MOCK-TRACK-' . uniqid(),
            'pdf_url' => 'assets/docs/boleta_mock_example.pdf',
            'xml_url' => 'assets/docs/boleta_mock_example.xml'
        ];
    }

    public function emitirFactura(array $datosVenta, array $datosCliente): array {
        $folio = rand(1000, 15000);
        return [
            'success' => true,
            'folio' => $folio,
            'track_id' => 'MOCK-TRACK-' . uniqid(),
            'pdf_url' => 'assets/docs/factura_mock_example.pdf',
            'xml_url' => 'assets/docs/factura_mock_example.xml'
        ];
    }

    public function emitirNotaCredito(array $datosDevolucion, int $folioReferencia): array {
        $folio = rand(500, 5000);
        return [
            'success' => true,
            'folio' => $folio,
            'track_id' => 'MOCK-TRACK-' . uniqid(),
            'pdf_url' => 'assets/docs/nc_mock_example.pdf',
            'xml_url' => 'assets/docs/nc_mock_example.xml'
        ];
    }
}

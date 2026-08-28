<?php
interface SiiDriverInterface {
    /**
     * Emitir Boleta Electrónica
     * @param array $datosVenta
     * @return array ['success' => bool, 'folio' => int, 'track_id' => string, 'pdf_url' => string, 'xml_url' => string, 'error' => string]
     */
    public function emitirBoleta(array $datosVenta): array;

    /**
     * Emitir Factura Electrónica
     * @param array $datosVenta
     * @param array $datosCliente
     * @return array
     */
    public function emitirFactura(array $datosVenta, array $datosCliente): array;

    /**
     * Emitir Nota de Crédito
     * @param array $datosDevolucion
     * @param int $folioReferencia
     * @return array
     */
    public function emitirNotaCredito(array $datosDevolucion, int $folioReferencia): array;
}

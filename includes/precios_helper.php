<?php
/**
 * Reglas de precio de venta, compartidas por api/registrar_venta.php y
 * api/sincronizar_offline.php para que una venta online y una offline cobren igual.
 *
 * El precio que envía la caja solo se acepta para productos de Precio Variable
 * (el cajero lo digita en el POS). Para el resto, el precio sale siempre del
 * catálogo, de modo que no se pueda vender a otro valor manipulando la petición.
 */

if (!function_exists('obtenerPromoActiva')) {
    function obtenerPromoActiva(PDO $pdo, int $productoId) {
        $stmt = $pdo->prepare("
            SELECT Tipo, CantidadMinima, DescuentoPorcentaje, PrecioOferta
            FROM promociones
            WHERE ProductoID = :pid AND Activa = TRUE AND FechaInicio <= NOW() AND FechaFin >= NOW()
            LIMIT 1
        ");
        $stmt->execute([':pid' => $productoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

if (!function_exists('resolverPrecioUnitario')) {
    /**
     * @param array      $prod          Fila de productos (requiere ProductoID, PrecioVenta, EsPrecioVariable)
     * @param float      $factor        Unidades por presentación (1 = unidad suelta, 6 = pack de 6...)
     * @param mixed      $precioCliente Precio enviado por la caja (solo vale para Precio Variable)
     * @param array|null $promo         Promoción activa del producto (obtenerPromoActiva)
     */
    function resolverPrecioUnitario(PDO $pdo, array $prod, float $factor, $precioCliente, ?array $promo): int {
        $precioBase = (int)$prod['PrecioVenta'];

        // Precio Variable / Abierto: manda el precio acordado en caja
        if ((int)($prod['EsPrecioVariable'] ?? 0) === 1 && (int)$precioCliente > 0) {
            return (int)$precioCliente;
        }

        if ($factor <= 1) {
            return $precioBase;
        }

        // Presentación pack/caja (código alternativo con factor de conversión)
        $stmtAlt = $pdo->prepare("SELECT PrecioVenta FROM productoscodigos WHERE ProductoID = :pid AND Cantidad = :factor LIMIT 1");
        $stmtAlt->execute([':pid' => $prod['ProductoID'], ':factor' => $factor]);
        $altPrecio = $stmtAlt->fetchColumn();

        if ($altPrecio !== false && $altPrecio !== null && (int)$altPrecio > 0) {
            // 1. Precio fijo explícito configurado en el código alternativo
            return (int)$altPrecio;
        }
        if ($promo && $promo['Tipo'] === 'MULTIBUY' && (float)$promo['CantidadMinima'] > 0
            && $factor >= (float)$promo['CantidadMinima'] && fmod($factor, (float)$promo['CantidadMinima']) == 0) {
            // 2. Heredar el precio de la promoción MULTIBUY activa
            return (int)round(($factor / (float)$promo['CantidadMinima']) * (int)$promo['PrecioOferta']);
        }
        if ($promo && $promo['Tipo'] === 'DESCUENTO_UNIT' && (float)$promo['DescuentoPorcentaje'] > 0) {
            // 2b. Heredar el descuento porcentual unitario
            $descUnit = (int)round($precioBase * ((float)$promo['DescuentoPorcentaje'] / 100));
            return (int)round(($precioBase - $descUnit) * $factor);
        }
        // 3. Sin oferta: precio base por la cantidad del pack
        return (int)round($precioBase * $factor);
    }
}

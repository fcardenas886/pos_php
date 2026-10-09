<?php
/**
 * Helper para el cálculo y generación de códigos de barra EAN-8 estándar GS1.
 * 
 * Estructura EAN-8:
 * - Dígito 1: Prefijo de tienda interna ('2')
 * - Dígitos 2-7: Secuencia numérica interna (6 dígitos)
 * - Dígito 8: Dígito verificador oficial GS1 Módulo 10 (ponderación 3, 1, 3, 1, 3, 1, 3)
 */

if (!function_exists('calcularDigitoEAN8')) {
    function calcularDigitoEAN8(string $sieteDigitos): int {
        $limpio = preg_replace('/\D/', '', $sieteDigitos);
        if (strlen($limpio) < 7) {
            $limpio = str_pad($limpio, 7, '0', STR_PAD_LEFT);
        } else {
            $limpio = substr($limpio, 0, 7);
        }

        $weights = [3, 1, 3, 1, 3, 1, 3];
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += (int)$limpio[$i] * $weights[$i];
        }

        return (10 - ($sum % 10)) % 10;
    }
}

if (!function_exists('esCodigoEAN8Valido')) {
    function esCodigoEAN8Valido(string $codigo): bool {
        $limpio = preg_replace('/\D/', '', $codigo);
        if (strlen($limpio) !== 8) {
            return false;
        }
        $siete = substr($limpio, 0, 7);
        $checkEsperado = calcularDigitoEAN8($siete);
        return (int)$limpio[7] === $checkEsperado;
    }
}

if (!function_exists('generarSiguienteEAN8')) {
    function generarSiguienteEAN8(PDO $pdo): string {
        // 1. Buscar el último código interno asignado que empiece con '2' y tenga exactamente 8 dígitos
        $stmt = $pdo->query("
            SELECT CodigoBarras 
            FROM productos 
            WHERE CodigoBarras REGEXP '^2[0-9]{7}$'
            ORDER BY CodigoBarras DESC 
            LIMIT 1
        ");
        $ultimo = $stmt ? $stmt->fetchColumn() : null;

        $nextSecuencia = 1;
        if ($ultimo && strlen($ultimo) === 8) {
            // Extraer los 6 dígitos del medio
            $secuencia = (int)substr($ultimo, 1, 6);
            $nextSecuencia = $secuencia + 1;
        } else {
            // Si no hay ninguno con formato 2XXXXXXC, comenzar con base en el mayor ProductoID
            $maxId = (int)$pdo->query("SELECT COALESCE(MAX(ProductoID), 0) FROM productos")->fetchColumn();
            $nextSecuencia = max(1, $maxId + 1);
        }

        // 2. Garantizar que no colisione con nada en productos ni en productoscodigos
        do {
            $base7 = '2' . str_pad((string)$nextSecuencia, 6, '0', STR_PAD_LEFT);
            $checkDigit = calcularDigitoEAN8($base7);
            $codigoCompleto = $base7 . $checkDigit;

            $stmtCheck = $pdo->prepare("
                SELECT 1 FROM productos WHERE CodigoBarras = :c1
                UNION
                SELECT 1 FROM productoscodigos WHERE CodigoBarras = :c2
            ");
            $stmtCheck->execute([':c1' => $codigoCompleto, ':c2' => $codigoCompleto]);
            $existe = (bool)$stmtCheck->fetchColumn();

            if ($existe) {
                $nextSecuencia++;
            }
        } while ($existe);

        return $codigoCompleto;
    }
}

/**
 * Código PLU para productos pesables (siempre 4 dígitos), según el tipo de balanza:
 * - 'manual' (sin etiqueta): ProductoID * 100 (ej. ID 12 -> "1200"), un código fácil de
 *   digitar en el POS. Si no cae entre 1000 y 9999 (ID < 10 o ID >= 100), se usa el
 *   primer número libre desde 1000.
 * - 'etiqueta' (EAN-13 20 PPPP ...): ProductoID con 4 dígitos (ej. ID 12 -> "0012").
 *   Si el ID pasa de 9999, se usa el primer número libre desde 0001.
 * Si el candidato ya está ocupado por otro producto, avanza al siguiente libre.
 */
if (!function_exists('generarCodigoPLU')) {
    function generarCodigoPLU(PDO $pdo, int $productoId, string $modoBalanza): string {
        $minimo = $modoBalanza === 'manual' ? 1000 : 1;
        $candidato = $modoBalanza === 'manual' ? $productoId * 100 : $productoId;
        if ($candidato < $minimo || $candidato > 9999) {
            $candidato = $minimo;
        }

        $stmtCheck = $pdo->prepare("
            SELECT 1 FROM productos
            WHERE (CodigoPLU = :plu OR CodigoBarras = :cod) AND ProductoID <> :id
            LIMIT 1
        ");
        // Recorre como máximo todo el rango, volviendo al mínimo al pasar de 9999
        for ($intentos = 0; $intentos <= 9999 - $minimo; $intentos++) {
            $plu = str_pad((string)$candidato, 4, '0', STR_PAD_LEFT);
            $stmtCheck->execute([':plu' => $plu, ':cod' => $plu, ':id' => $productoId]);
            if (!$stmtCheck->fetchColumn()) {
                return $plu;
            }
            $candidato = $candidato >= 9999 ? $minimo : $candidato + 1;
        }
        throw new Exception('No quedan códigos PLU de 4 dígitos disponibles.');
    }
}

if (!function_exists('obtenerModoBalanza')) {
    function obtenerModoBalanza(PDO $pdo): string {
        $modo = $pdo->query("SELECT Valor FROM configuraciones WHERE Clave = 'BALANZA_MODO'")->fetchColumn();
        return $modo === 'manual' ? 'manual' : 'etiqueta';
    }
}

<?php

namespace App\Facturacion\Paises\Peru;

/**
 * Convierte un importe a su representación en letras para la leyenda 1000
 * exigida por SUNAT (p. ej. "SON CIENTO VEINTE CON 00/100 SOLES").
 */
class NumberToLetters
{
    private const UNIDADES = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    private const DIEZ_A_DIECINUEVE = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    private const DECENAS = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    private const CENTENAS = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

    public function convertir(float $monto, string $moneda = 'SOLES'): string
    {
        $entero = (int) floor($monto);
        $decimal = (int) round(($monto - $entero) * 100);

        $letras = $entero === 0 ? 'CERO' : trim($this->seccion($entero));

        return sprintf('SON %s CON %02d/100 %s', $letras, $decimal, $moneda);
    }

    private function seccion(int $n): string
    {
        if ($n === 0) {
            return '';
        }
        if ($n === 100) {
            return 'CIEN';
        }
        if ($n < 10) {
            return self::UNIDADES[$n];
        }
        if ($n < 20) {
            return self::DIEZ_A_DIECINUEVE[$n - 10];
        }
        if ($n < 30) {
            return $n === 20 ? 'VEINTE' : 'VEINTI'.strtolower(self::UNIDADES[$n - 20]);
        }
        if ($n < 100) {
            $d = self::DECENAS[intdiv($n, 10)];
            $u = $n % 10;

            return $u ? $d.' Y '.self::UNIDADES[$u] : $d;
        }
        if ($n < 1000) {
            return trim(self::CENTENAS[intdiv($n, 100)].' '.$this->seccion($n % 100));
        }
        if ($n < 1000000) {
            $miles = intdiv($n, 1000);
            $prefijo = $miles === 1 ? 'MIL' : trim($this->seccion($miles)).' MIL';

            return trim($prefijo.' '.$this->seccion($n % 1000));
        }
        $millones = intdiv($n, 1000000);
        $prefijo = $millones === 1 ? 'UN MILLON' : trim($this->seccion($millones)).' MILLONES';

        return trim($prefijo.' '.$this->seccion($n % 1000000));
    }
}

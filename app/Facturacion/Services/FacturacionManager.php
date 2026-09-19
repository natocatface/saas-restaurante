<?php

namespace App\Facturacion\Services;

use App\Facturacion\Contracts\FacturadorPais;
use App\Facturacion\Data\ComprobanteData;
use App\Facturacion\Data\ResultadoFacturacion;
use App\Facturacion\Enums\Pais;
use App\Facturacion\Exceptions\PaisNoSoportadoException;

/**
 * Punto de entrada del dominio de facturación (fachada + registry).
 *
 * El ERP habla SOLO con esta clase. Internamente resuelve la estrategia
 * concreta (adaptador de país) y delega. Sumar un país no cambia esta clase:
 * basta registrar el nuevo adaptador en el ServiceProvider.
 */
class FacturacionManager
{
    /** @var array<string, FacturadorPais> */
    private array $adaptadores = [];

    /** Registra (o reemplaza) el adaptador de un país. */
    public function registrar(FacturadorPais $facturador): void
    {
        $this->adaptadores[$facturador->pais()->value] = $facturador;
    }

    public function soporta(Pais|string $pais): bool
    {
        $codigo = $pais instanceof Pais ? $pais->value : $pais;

        return isset($this->adaptadores[$codigo]);
    }

    public function para(Pais|string $pais): FacturadorPais
    {
        $codigo = $pais instanceof Pais ? $pais->value : $pais;

        return $this->adaptadores[$codigo]
            ?? throw PaisNoSoportadoException::para($pais);
    }

    /** @return Pais[] */
    public function paisesDisponibles(): array
    {
        return array_map(fn ($c) => Pais::from($c), array_keys($this->adaptadores));
    }

    /* ---- API de conveniencia: delega en la estrategia del comprobante ---- */

    public function emitir(ComprobanteData $c): ResultadoFacturacion
    {
        return $this->para($c->pais)->emitir($c);
    }

    public function anular(ComprobanteData $c, string $motivo): ResultadoFacturacion
    {
        return $this->para($c->pais)->anular($c, $motivo);
    }

    public function emitirNotaCredito(ComprobanteData $c): ResultadoFacturacion
    {
        return $this->para($c->pais)->emitirNotaCredito($c);
    }

    public function consultarEstado(ComprobanteData $c): ResultadoFacturacion
    {
        return $this->para($c->pais)->consultarEstado($c);
    }
}

<?php

namespace App\Facturacion\Jobs;

use App\Facturacion\Data\ComprobanteData;
use App\Facturacion\Services\EmisorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Emite un comprobante de forma asíncrona. Ideal para no bloquear el POS
 * cuando la autoridad fiscal está lenta o caída.
 *
 * Reintentos con backoff exponencial: 10s, 30s, 2min. Tras agotarlos, el
 * comprobante queda en estado 'error' y visible para reintento manual.
 */
class EmitirComprobanteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;
    public array $backoff = [10, 30, 120];

    public function __construct(
        public readonly int $restauranteId,
        public readonly ComprobanteData $data,
    ) {}

    public function handle(EmisorService $emisor): void
    {
        // Fija el tenant en el contexto del worker (fuera de una request web).
        app()->instance('currentTenantId', $this->restauranteId);

        $emisor->emitir($this->data);
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(6);
    }
}

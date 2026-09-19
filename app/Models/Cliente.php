<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Cliente extends Model
{
    use HasFactory, BelongsToTenant;

    /** Reglas de fidelización. */
    public const SOLES_POR_PUNTO = 10;   // 1 punto por cada S/ 10 de consumo
    public const VALOR_PUNTO = 0.10;     // cada punto vale S/ 0.10 al canjear

    protected $table = 'clientes';

    protected $fillable = [
        'restaurante_id', 'nombre', 'documento', 'telefono', 'email', 'direccion', 'puntos',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function movimientosPuntos()
    {
        return $this->hasMany(ClientePunto::class);
    }

    /** Acredita puntos por el monto consumido y registra el movimiento. */
    public function acreditarPuntos(float $monto, ?Pedido $pedido = null): int
    {
        $ganados = (int) floor($monto / self::SOLES_POR_PUNTO);
        if ($ganados <= 0) {
            return 0;
        }
        $this->increment('puntos', $ganados);
        $this->movimientosPuntos()->create([
            'pedido_id'   => $pedido?->id,
            'tipo'        => 'ganado',
            'puntos'      => $ganados,
            'valor'       => 0,
            'descripcion' => $pedido ? "Consumo {$pedido->codigo}" : 'Consumo',
        ]);
        return $ganados;
    }

    /** Canjea puntos como descuento. Devuelve el valor en dinero canjeado. */
    public function canjearPuntos(int $puntos, ?Pedido $pedido = null): float
    {
        $puntos = min($puntos, (int) $this->puntos);
        if ($puntos <= 0) {
            return 0;
        }
        $valor = round($puntos * self::VALOR_PUNTO, 2);
        $this->decrement('puntos', $puntos);
        $this->movimientosPuntos()->create([
            'pedido_id'   => $pedido?->id,
            'tipo'        => 'canjeado',
            'puntos'      => $puntos,
            'valor'       => $valor,
            'descripcion' => $pedido ? "Canje en {$pedido->codigo}" : 'Canje de puntos',
        ]);
        return $valor;
    }
}

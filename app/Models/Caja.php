<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'cajas';

    protected $fillable = [
        'restaurante_id', 'user_id', 'cerrada_por', 'estado', 'monto_inicial',
        'efectivo_esperado', 'monto_contado', 'diferencia',
        'notas_apertura', 'notas_cierre', 'abierta_at', 'cerrada_at',
    ];

    protected $casts = [
        'monto_inicial'     => 'decimal:2',
        'efectivo_esperado' => 'decimal:2',
        'monto_contado'     => 'decimal:2',
        'diferencia'        => 'decimal:2',
        'abierta_at'        => 'datetime',
        'cerrada_at'        => 'datetime',
    ];

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cerradaPor()
    {
        return $this->belongsTo(User::class, 'cerrada_por');
    }

    public function abierta(): bool
    {
        return $this->estado === 'abierta';
    }

    /** Caja abierta del tenant actual (o null). */
    public static function abiertaActual(): ?self
    {
        return static::where('estado', 'abierta')->latest('abierta_at')->first();
    }

    /** Pedidos pagados dentro del periodo de esta caja, agrupados por método de pago. */
    public function ventasPorMetodo()
    {
        $hasta = $this->cerrada_at ?? now();

        return Pedido::where('estado', 'pagado')
            ->whereBetween('pagado_at', [$this->abierta_at, $hasta])
            ->selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy('metodo_pago')
            ->pluck('total', 'metodo_pago');
    }

    /** Resumen financiero en vivo de la caja. */
    public function resumen(): array
    {
        $ventas    = $this->ventasPorMetodo();
        $ventasTot = (float) $ventas->sum();
        $efeVentas = (float) ($ventas['efectivo'] ?? 0);

        $ingresos = (float) $this->movimientos()->where('tipo', 'ingreso')->sum('monto');
        $egresos  = (float) $this->movimientos()->where('tipo', 'egreso')->sum('monto');

        $efectivoEsperado = (float) $this->monto_inicial + $efeVentas + $ingresos - $egresos;

        return [
            'ventas'            => $ventas,
            'ventas_total'      => $ventasTot,
            'ventas_efectivo'   => $efeVentas,
            'ingresos'          => $ingresos,
            'egresos'           => $egresos,
            'efectivo_esperado' => round($efectivoEsperado, 2),
        ];
    }
}

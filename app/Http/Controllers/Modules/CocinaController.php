<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class CocinaController extends Controller
{
    /** Pantalla del tablero de cocina (KDS). */
    public function index()
    {
        return view('modules.cocina.index');
    }

    /** Devuelve en JSON los pedidos activos para refresco en tiempo real. */
    public function data()
    {
        $activos = Pedido::with('mesa', 'items')
            ->whereIn('estado', ['pendiente', 'preparando'])
            ->orderBy('created_at')
            ->get();

        // Servidos recientemente (últimos 20 min) para la columna "Listos".
        $listos = Pedido::with('mesa', 'items')
            ->where('estado', 'servido')
            ->where('updated_at', '>=', now()->subMinutes(20))
            ->orderByDesc('updated_at')
            ->get();

        $map = fn (Pedido $p) => [
            'id'         => $p->id,
            'codigo'     => $p->codigo,
            'tipo'       => $p->tipo,
            'mesa'       => $p->mesa?->numero,
            'estado'     => $p->estado,
            'notas'      => $p->notas,
            'creado'     => $p->created_at->toIso8601String(),
            'minutos'    => (int) $p->created_at->diffInMinutes(now()),
            'items'      => $p->items->map(fn ($i) => [
                'nombre'   => $i->nombre_producto,
                'cantidad' => $i->cantidad,
                'notas'    => $i->notas,
            ])->values(),
        ];

        return response()->json([
            'pendientes' => $activos->where('estado', 'pendiente')->map($map)->values(),
            'preparando' => $activos->where('estado', 'preparando')->map($map)->values(),
            'listos'     => $listos->map($map)->values(),
            'counts'     => [
                'pendiente'  => $activos->where('estado', 'pendiente')->count(),
                'preparando' => $activos->where('estado', 'preparando')->count(),
            ],
            'ts' => now()->toIso8601String(),
        ]);
    }

    /** Avanza el pedido al siguiente estado de cocina. */
    public function avanzar(Pedido $pedido)
    {
        $siguiente = match ($pedido->estado) {
            'pendiente'  => 'preparando',
            'preparando' => 'servido',
            default      => $pedido->estado,
        };
        $pedido->update(['estado' => $siguiente]);

        return response()->json(['ok' => true, 'estado' => $siguiente]);
    }

    /** Cancela un pedido desde cocina y libera la mesa si corresponde. */
    public function cancelar(Pedido $pedido)
    {
        if ($pedido->estado !== 'cancelado') {
            $pedido->restaurarInventario();
        }
        $pedido->update(['estado' => 'cancelado']);
        if ($pedido->mesa) {
            $pedido->mesa->update(['estado' => 'libre']);
        }

        return response()->json(['ok' => true, 'estado' => 'cancelado']);
    }
}

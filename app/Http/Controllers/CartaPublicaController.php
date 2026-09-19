<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartaPublicaController extends Controller
{
    /** Carta pública del restaurante (sin login, vía QR). */
    public function show(Request $request, Restaurante $restaurante)
    {
        abort_unless($restaurante->activo, 404);

        // Ruta pública: no hay tenant activo, así que filtramos explícitamente por restaurante.
        $categorias = Categoria::where('restaurante_id', $restaurante->id)
            ->where('activo', true)
            ->with(['productos' => function ($q) use ($restaurante) {
                $q->where('restaurante_id', $restaurante->id)
                  ->where('disponible', true)
                  ->orderBy('nombre');
            }])
            ->orderBy('orden')
            ->get()
            ->filter(fn ($c) => $c->productos->isNotEmpty())
            ->values();

        // Mesa opcional desde el QR (?mesa=NUMERO)
        $mesa = $request->query('mesa');

        return view('carta_publica', compact('restaurante', 'categorias', 'mesa'));
    }

    /** Recibe un pedido hecho por el cliente desde la carta (sin login). */
    public function pedido(Request $request, Restaurante $restaurante)
    {
        abort_unless($restaurante->activo, 404);

        $data = $request->validate([
            'nombre'            => 'nullable|string|max:255',
            'mesa'              => 'nullable|string|max:30',
            'tipo'              => 'required|in:mesa,llevar',
            'notas'             => 'nullable|string|max:500',
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|integer',
            'items.*.cantidad'  => 'required|integer|min:1',
        ]);

        $pedido = DB::transaction(function () use ($data, $restaurante) {
            $mesaId = null;
            if ($data['tipo'] === 'mesa' && ! empty($data['mesa'])) {
                $mesaId = Mesa::where('restaurante_id', $restaurante->id)
                    ->where('numero', $data['mesa'])->value('id');
            }

            $notas = trim(($data['nombre'] ? "Cliente: {$data['nombre']}. " : '').($data['notas'] ?? ''));

            $pedido = new Pedido([
                'codigo'      => 'WEB-'.now()->format('ymd').'-'.str_pad((string) (Pedido::where('restaurante_id', $restaurante->id)->whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT),
                'mesa_id'     => $mesaId,
                'tipo'        => $data['tipo'],
                'estado'      => 'pendiente',
                'notas'       => $notas ?: null,
            ]);
            $pedido->restaurante_id = $restaurante->id;  // ruta pública: asignar tenant manualmente
            $pedido->save();

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $prod = Producto::where('restaurante_id', $restaurante->id)
                    ->where('disponible', true)->find($item['id']);
                if (! $prod) {
                    continue;
                }
                $sub = (float) $prod->precio * $item['cantidad'];
                $subtotal += $sub;

                $pi = new PedidoItem([
                    'pedido_id'       => $pedido->id,
                    'producto_id'     => $prod->id,
                    'nombre_producto' => $prod->nombre,
                    'cantidad'        => $item['cantidad'],
                    'precio'          => $prod->precio,
                    'subtotal'        => $sub,
                ]);
                $pi->restaurante_id = $restaurante->id;
                $pi->save();

                $prod->increment('vendidos', $item['cantidad']);
            }

            $impuesto = round($subtotal * ((float) $restaurante->igv / 100), 2);
            $pedido->update(['subtotal' => $subtotal, 'impuesto' => $impuesto, 'total' => $subtotal + $impuesto]);

            if ($mesaId) {
                Mesa::where('id', $mesaId)->update(['estado' => 'ocupada']);
            }

            return $pedido;
        });

        return redirect()
            ->route('carta.publica', $restaurante->slug)
            ->with('pedido_ok', $pedido->codigo);
    }
}

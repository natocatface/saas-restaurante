<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Restaurante;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $pedidos = Pedido::with('mesa', 'user', 'cliente')
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->when($request->q, fn ($q) => $q->where('codigo', 'like', "%{$request->q}%"))
            ->latest()->paginate(15)->withQueryString();
        $resumen = [
            'pendiente' => Pedido::where('estado', 'pendiente')->count(),
            'preparando' => Pedido::where('estado', 'preparando')->count(),
            'servido' => Pedido::where('estado', 'servido')->count(),
            'pagado_hoy' => Pedido::where('estado', 'pagado')->whereDate('pagado_at', today())->count(),
        ];
        return view('modules.pedidos.index', compact('pedidos', 'resumen'));
    }

    public function show(Pedido $pedido)
    {
        $pedido->load('items', 'mesa', 'user', 'cliente');
        return view('modules.pedidos.show', compact('pedido'));
    }

    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'estado' => 'required|in:pendiente,preparando,servido,pagado,cancelado',
            'metodo_pago' => 'nullable|in:efectivo,tarjeta,yape,plin,transferencia',
        ]);

        $previo = $pedido->estado;
        $pedido->estado = $data['estado'];

        if ($data['estado'] === 'cancelado' && $previo !== 'cancelado') {
            $pedido->restaurarInventario();
            if ($pedido->mesa) {
                $pedido->mesa->update(['estado' => 'libre']);
            }
        }

        if ($data['estado'] === 'pagado') {
            $pedido->pagado_at = now();
            $pedido->metodo_pago = $data['metodo_pago'] ?? $pedido->metodo_pago ?? 'efectivo';
            if ($pedido->mesa) {
                $pedido->mesa->update(['estado' => 'libre']);
            }
        }
        $pedido->save();

        // Acreditar puntos de fidelización (una sola vez)
        if ($data['estado'] === 'pagado' && $previo !== 'pagado' && $pedido->cliente_id && (int) $pedido->puntos_ganados === 0) {
            $ganados = $pedido->cliente->acreditarPuntos((float) $pedido->total, $pedido);
            $pedido->update(['puntos_ganados' => $ganados]);
        }

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function ticket(Pedido $pedido)
    {
        $pedido->load('items', 'mesa', 'user', 'cliente');
        $config = Restaurante::actual();
        return view('modules.pedidos.ticket', compact('pedido', 'config'));
    }

    public function destroy(Pedido $pedido)
    {
        $pedido->delete();
        return back()->with('success', 'Pedido eliminado.');
    }
}

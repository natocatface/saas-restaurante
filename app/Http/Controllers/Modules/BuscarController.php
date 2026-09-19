<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;

class BuscarController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['grupos' => []]);
        }
        $like = "%{$q}%";

        $productos = Producto::where('nombre', 'like', $like)->orWhere('sku', 'like', $like)
            ->take(5)->get()->map(fn ($p) => [
                'titulo' => $p->nombre,
                'detalle' => 'S/ '.number_format($p->precio, 2),
                'url' => route('productos.edit', $p),
            ]);

        $mesas = Mesa::where('nombre', 'like', $like)->orWhere('numero', 'like', $like)
            ->take(5)->get()->map(fn ($m) => [
                'titulo' => $m->nombre ?? ('Mesa '.$m->numero),
                'detalle' => ucfirst($m->estado),
                'url' => route('mesas.index'),
            ]);

        $pedidos = Pedido::where('codigo', 'like', $like)
            ->latest()->take(5)->get()->map(fn ($p) => [
                'titulo' => $p->codigo,
                'detalle' => ucfirst($p->estado).' · S/ '.number_format($p->total, 2),
                'url' => route('pedidos.show', $p),
            ]);

        $clientes = Cliente::where('nombre', 'like', $like)->orWhere('documento', 'like', $like)
            ->take(5)->get()->map(fn ($c) => [
                'titulo' => $c->nombre,
                'detalle' => $c->documento ?: ($c->telefono ?: '—'),
                'url' => route('clientes.edit', $c),
            ]);

        $grupos = collect([
            ['nombre' => 'Productos', 'items' => $productos],
            ['nombre' => 'Mesas', 'items' => $mesas],
            ['nombre' => 'Pedidos', 'items' => $pedidos],
            ['nombre' => 'Clientes', 'items' => $clientes],
        ])->filter(fn ($g) => $g['items']->isNotEmpty())->values();

        return response()->json(['grupos' => $grupos]);
    }
}

<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\InsumoMovimiento;
use Illuminate\Http\Request;

class KardexController extends Controller
{
    public function index(Request $request)
    {
        $insumos = Insumo::orderBy('nombre')->get();

        $movimientos = InsumoMovimiento::with('insumo', 'usuario')
            ->when($request->insumo_id, fn ($q) => $q->where('insumo_id', $request->insumo_id))
            ->when($request->tipo, fn ($q) => $q->where('tipo', $request->tipo))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('modules.kardex.index', compact('insumos', 'movimientos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'insumo_id' => 'required|exists:insumos,id',
            'tipo'      => 'required|in:entrada,ajuste',
            'cantidad'  => 'required|numeric|min:0',
            'motivo'    => 'nullable|string|max:255',
        ]);

        $insumo = Insumo::findOrFail($data['insumo_id']);

        $motivoDefault = $data['tipo'] === 'entrada' ? 'Compra / ingreso de stock' : 'Ajuste manual de inventario';

        InsumoMovimiento::registrar(
            $insumo,
            $data['tipo'],
            (float) $data['cantidad'],
            $data['motivo'] ?: $motivoDefault,
            null,
            $request->user()->id
        );

        return back()->with('success', 'Movimiento de inventario registrado.');
    }
}

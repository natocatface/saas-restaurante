<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    public function index(Request $request)
    {
        $insumos = Insumo::when($request->q, fn ($q) => $q->where('nombre', 'like', "%{$request->q}%"))
            ->orderBy('nombre')->paginate(15)->withQueryString();
        $bajoStock = Insumo::whereColumn('stock', '<=', 'stock_minimo')->count();
        return view('modules.insumos.index', compact('insumos', 'bajoStock'));
    }

    public function create()
    {
        return view('modules.insumos.form', ['insumo' => new Insumo(['unidad' => 'unidad'])]);
    }

    public function store(Request $request)
    {
        Insumo::create($this->validateData($request));
        return redirect()->route('insumos.index')->with('success', 'Insumo registrado.');
    }

    public function edit(Insumo $insumo)
    {
        return view('modules.insumos.form', compact('insumo'));
    }

    public function update(Request $request, Insumo $insumo)
    {
        $insumo->update($this->validateData($request));
        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado.');
    }

    public function destroy(Insumo $insumo)
    {
        $insumo->delete();
        return back()->with('success', 'Insumo eliminado.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad' => 'required|string|max:20',
            'stock' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
            'proveedor' => 'nullable|string|max:255',
        ]);
    }
}

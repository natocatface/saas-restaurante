<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecetaController extends Controller
{
    public function edit(Producto $producto)
    {
        $producto->load('recetas.insumo', 'categoria');
        $insumos = Insumo::orderBy('nombre')->get();

        return view('modules.recetas.edit', compact('producto', 'insumos'));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'items'              => 'array',
            'items.*.insumo_id'  => 'required|exists:insumos,id',
            'items.*.cantidad'   => 'required|numeric|min:0',
            'actualizar_costo'   => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($data, $producto, $request) {
            $producto->recetas()->delete();

            $vistos = [];
            foreach ($data['items'] ?? [] as $item) {
                if ((float) $item['cantidad'] <= 0) {
                    continue;
                }
                if (in_array($item['insumo_id'], $vistos)) {
                    continue; // evita insumos duplicados
                }
                $vistos[] = $item['insumo_id'];

                $producto->recetas()->create([
                    'insumo_id' => $item['insumo_id'],
                    'cantidad'  => $item['cantidad'],
                ]);
            }

            // Opcional: fijar el costo del producto = costo de la receta.
            if ($request->boolean('actualizar_costo')) {
                $producto->load('recetas.insumo');
                $producto->update(['costo' => $producto->costoReceta()]);
            }
        });

        return redirect()->route('productos.index')->with('success', "Receta de «{$producto->nombre}» guardada.");
    }
}

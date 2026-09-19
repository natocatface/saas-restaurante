<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromocionController extends Controller
{
    public function index()
    {
        $promociones = Promocion::with('producto')->latest()->paginate(15);
        return view('modules.promociones.index', compact('promociones'));
    }

    public function create()
    {
        return view('modules.promociones.form', [
            'promocion' => new Promocion(['tipo' => 'porcentaje', 'alcance' => 'total', 'activo' => true]),
            'productos' => Producto::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Promocion::create($this->validar($request));
        return redirect()->route('promociones.index')->with('success', 'Promoción creada.');
    }

    public function edit(Promocion $promocion)
    {
        return view('modules.promociones.form', [
            'promocion' => $promocion,
            'productos' => Producto::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Promocion $promocion)
    {
        $promocion->update($this->validar($request));
        return redirect()->route('promociones.index')->with('success', 'Promoción actualizada.');
    }

    public function destroy(Promocion $promocion)
    {
        $promocion->delete();
        return back()->with('success', 'Promoción eliminada.');
    }

    private function validar(Request $request): array
    {
        $data = $request->validate([
            'nombre'     => 'required|string|max:255',
            'codigo'     => 'nullable|string|max:40',
            'tipo'       => 'required|in:porcentaje,monto',
            'valor'      => 'required|numeric|min:0',
            'alcance'    => 'required|in:total,producto',
            'producto_id'=> 'nullable|exists:productos,id|required_if:alcance,producto',
            'min_compra' => 'nullable|numeric|min:0',
            'inicia_at'  => 'nullable|date',
            'termina_at' => 'nullable|date|after_or_equal:inicia_at',
            'activo'     => 'nullable|boolean',
        ]);
        $data['activo'] = $request->boolean('activo');
        if ($data['alcance'] === 'total') {
            $data['producto_id'] = null;
        }
        return $data;
    }
}

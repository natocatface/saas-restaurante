<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $productos = Producto::with('categoria')
            ->when($request->q, fn ($query) => $query->where('nombre', 'like', "%{$request->q}%"))
            ->when($request->categoria, fn ($query) => $query->where('categoria_id', $request->categoria))
            ->latest()->paginate(12)->withQueryString();
        $categorias = Categoria::orderBy('nombre')->get();
        return view('modules.productos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        return view('modules.productos.form', [
            'producto' => new Producto(['disponible' => true]),
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $rest = \App\Models\Restaurante::actual();
        if ($rest && $rest->limiteAlcanzado('productos', Producto::count())) {
            return back()->withInput()->with('error', 'Alcanzaste el límite de productos de tu plan. Mejora tu plan en Suscripción para agregar más.');
        }
        Producto::create($this->validateData($request));
        return redirect()->route('productos.index')->with('success', 'Producto agregado a la carta.');
    }

    public function edit(Producto $producto)
    {
        return view('modules.productos.form', [
            'producto' => $producto,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $producto->update($this->validateData($request));
        return redirect()->route('productos.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer',
        ]);
        $data['disponible'] = $request->boolean('disponible');
        $data['controla_stock'] = $request->boolean('controla_stock');
        return $data;
    }
}

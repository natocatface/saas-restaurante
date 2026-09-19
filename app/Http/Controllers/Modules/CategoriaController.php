<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('productos')->orderBy('orden')->paginate(12);
        return view('modules.categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('modules.categorias.form', ['categoria' => new Categoria()]);
    }

    public function store(Request $request)
    {
        Categoria::create($this->validateData($request));
        return redirect()->route('categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        return view('modules.categorias.form', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $categoria->update($this->validateData($request));
        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->exists()) {
            return back()->with('error', 'No puedes eliminar una categoría con productos asociados.');
        }
        $categoria->delete();
        return back()->with('success', 'Categoría eliminada.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'icono' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'orden' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
        ]) + ['activo' => $request->boolean('activo')];
    }
}

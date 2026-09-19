<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Restaurante;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function edit()
    {
        $config = Restaurante::actual();
        return view('modules.configuracion.edit', compact('config'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nombre_restaurante' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'moneda' => 'required|string|max:10',
            'igv' => 'required|numeric|min:0|max:100',
            'meta_mensual' => 'required|numeric|min:0',
        ]);

        $data['nombre'] = $data['nombre_restaurante'];
        unset($data['nombre_restaurante']);

        Restaurante::actual()->update($data);

        return back()->with('success', 'Configuración guardada correctamente.');
    }
}

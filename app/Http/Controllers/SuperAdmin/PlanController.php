<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $planes = Plan::withCount('restaurantes')->orderBy('orden')->get();
        return view('superadmin.planes.index', compact('planes'));
    }

    public function create()
    {
        return view('superadmin.planes.form', ['plan' => new Plan(['intervalo' => 'mensual', 'activo' => true])]);
    }

    public function store(Request $request)
    {
        Plan::create($this->validateData($request));
        return redirect()->route('superadmin.planes.index')->with('success', 'Plan creado.');
    }

    public function edit(Plan $plan)
    {
        return view('superadmin.planes.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($this->validateData($request, $plan));
        return redirect()->route('superadmin.planes.index')->with('success', 'Plan actualizado.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->restaurantes()->exists()) {
            return back()->with('error', 'No puedes eliminar un plan con restaurantes asignados.');
        }
        $plan->delete();
        return back()->with('success', 'Plan eliminado.');
    }

    private function validateData(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'precio' => 'required|numeric|min:0',
            'intervalo' => 'required|in:mensual,anual',
            'max_mesas' => 'nullable|integer|min:0',
            'max_productos' => 'nullable|integer|min:0',
            'max_usuarios' => 'nullable|integer|min:0',
            'features' => 'nullable|string',
            'orden' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = $plan?->slug ?? Str::slug($data['nombre']).'-'.Str::random(4);
        $data['destacado'] = $request->boolean('destacado');
        $data['activo'] = $request->boolean('activo');
        // features: una por línea -> array
        $data['features'] = collect(preg_split('/\r\n|\r|\n/', (string) $request->features))
            ->map(fn ($f) => trim($f))->filter()->values()->all();

        return $data;
    }
}

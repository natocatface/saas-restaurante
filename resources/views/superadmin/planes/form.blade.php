<x-superadmin-layout :title="$plan->exists ? 'Editar plan' : 'Nuevo plan'">
    <div class="mb-6">
        <a href="{{ route('superadmin.planes.index') }}" class="text-sm font-semibold text-brand-600">← Planes</a>
        <h1 class="text-2xl font-extrabold text-slate-800">{{ $plan->exists ? 'Editar plan' : 'Nuevo plan' }}</h1>
    </div>

    <div class="card max-w-3xl">
        <x-val-errors />
        <form method="POST" action="{{ $plan->exists ? route('superadmin.planes.update', $plan) : route('superadmin.planes.store') }}" class="space-y-5">
            @csrf
            @if($plan->exists) @method('PUT') @endif
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre *</label>
                    <input name="nombre" value="{{ old('nombre', $plan->nombre) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Precio (S/) *</label>
                    <input type="number" step="0.01" name="precio" value="{{ old('precio', $plan->precio) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Intervalo *</label>
                    <select name="intervalo" class="form-input-c">
                        <option value="mensual" @selected(old('intervalo', $plan->intervalo)=='mensual')>Mensual</option>
                        <option value="anual" @selected(old('intervalo', $plan->intervalo)=='anual')>Anual</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Orden</label>
                    <input type="number" name="orden" value="{{ old('orden', $plan->orden ?? 0) }}" class="form-input-c">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Descripción</label>
                    <input name="descripcion" value="{{ old('descripcion', $plan->descripcion) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Máx. mesas</label>
                    <input type="number" name="max_mesas" value="{{ old('max_mesas', $plan->max_mesas) }}" class="form-input-c" placeholder="Vacío = ilimitado">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Máx. productos</label>
                    <input type="number" name="max_productos" value="{{ old('max_productos', $plan->max_productos) }}" class="form-input-c" placeholder="Vacío = ilimitado">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Máx. usuarios</label>
                    <input type="number" name="max_usuarios" value="{{ old('max_usuarios', $plan->max_usuarios) }}" class="form-input-c" placeholder="Vacío = ilimitado">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Características (una por línea)</label>
                    <textarea name="features" rows="4" class="form-input-c" placeholder="Soporte por correo&#10;Reportes avanzados">{{ old('features', is_array($plan->features) ? implode("\n", $plan->features) : '') }}</textarea>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="destacado" value="1" @checked(old('destacado', $plan->destacado)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                    Plan destacado (Más popular)
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="activo" value="1" @checked(old('activo', $plan->activo ?? true)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                    Plan activo (visible en precios)
                </label>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('superadmin.planes.index') }}" class="btn-secondary">Cancelar</a>
                <button class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</x-superadmin-layout>

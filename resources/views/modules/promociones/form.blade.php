<x-app-layout :title="$promocion->exists ? 'Editar promoción' : 'Nueva promoción'">
    <x-page-header :title="$promocion->exists ? 'Editar promoción' : 'Nueva promoción'">
        <a href="{{ route('promociones.index') }}" class="btn-secondary">← Volver</a>
    </x-page-header>

    <div class="card max-w-2xl" x-data="{ alcance: '{{ old('alcance', $promocion->alcance) }}' }">
        <x-val-errors />
        <form method="POST" action="{{ $promocion->exists ? route('promociones.update', $promocion) : route('promociones.store') }}" class="space-y-5">
            @csrf
            @if($promocion->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre *</label>
                    <input name="nombre" value="{{ old('nombre', $promocion->nombre) }}" class="form-input-c" required placeholder="Ej. Happy Hour, 2x1 bebidas...">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Código de cupón (opcional)</label>
                    <input name="codigo" value="{{ old('codigo', $promocion->codigo) }}" class="form-input-c" placeholder="Ej. VERANO10">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tipo *</label>
                    <select name="tipo" class="form-input-c">
                        <option value="porcentaje" @selected(old('tipo',$promocion->tipo)==='porcentaje')>Porcentaje (%)</option>
                        <option value="monto" @selected(old('tipo',$promocion->tipo)==='monto')>Monto fijo</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Valor *</label>
                    <input type="number" step="0.01" min="0" name="valor" value="{{ old('valor', $promocion->valor) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Compra mínima (opcional)</label>
                    <input type="number" step="0.01" min="0" name="min_compra" value="{{ old('min_compra', $promocion->min_compra) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Alcance *</label>
                    <select name="alcance" x-model="alcance" class="form-input-c">
                        <option value="total">Todo el pedido</option>
                        <option value="producto">Un producto</option>
                    </select>
                </div>
                <div x-show="alcance==='producto'">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Producto</label>
                    <select name="producto_id" class="form-input-c">
                        <option value="">Selecciona…</option>
                        @foreach($productos as $p)<option value="{{ $p->id }}" @selected(old('producto_id',$promocion->producto_id)==$p->id)>{{ $p->nombre }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Inicia</label>
                    <input type="date" name="inicia_at" value="{{ old('inicia_at', $promocion->inicia_at?->toDateString()) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Termina</label>
                    <input type="date" name="termina_at" value="{{ old('termina_at', $promocion->termina_at?->toDateString()) }}" class="form-input-c">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $promocion->activo)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                        Activa
                    </label>
                </div>
            </div>
            <div class="flex justify-end"><button class="btn-primary">{{ $promocion->exists ? 'Guardar cambios' : 'Crear promoción' }}</button></div>
        </form>
    </div>
</x-app-layout>

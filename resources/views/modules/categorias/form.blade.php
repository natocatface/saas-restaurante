<x-app-layout :title="$categoria->exists ? 'Editar categoría' : 'Nueva categoría'">
    <x-page-header :title="$categoria->exists ? 'Editar categoría' : 'Nueva categoría'">
        <a href="{{ route('categorias.index') }}" class="btn-secondary">← Volver</a>
    </x-page-header>

    <div class="card max-w-2xl">
        <x-val-errors />
        <form method="POST" action="{{ $categoria->exists ? route('categorias.update', $categoria) : route('categorias.store') }}" class="space-y-5">
            @csrf
            @if($categoria->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre *</label>
                    <input name="nombre" value="{{ old('nombre', $categoria->nombre) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Ícono (emoji)</label>
                    <input name="icono" value="{{ old('icono', $categoria->icono) }}" class="form-input-c" placeholder="🍛">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Color</label>
                    <input type="color" name="color" value="{{ old('color', $categoria->color ?: '#7257f0') }}" class="h-10 w-full rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Orden</label>
                    <input type="number" name="orden" value="{{ old('orden', $categoria->orden ?? 0) }}" class="form-input-c">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="activo" value="1" @checked(old('activo', $categoria->activo ?? true)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                        Categoría activa
                    </label>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Descripción</label>
                    <input name="descripcion" value="{{ old('descripcion', $categoria->descripcion) }}" class="form-input-c">
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('categorias.index') }}" class="btn-secondary">Cancelar</a>
                <button class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</x-app-layout>

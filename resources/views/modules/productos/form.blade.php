<x-app-layout :title="$producto->exists ? 'Editar producto' : 'Nuevo producto'">
    <x-page-header :title="$producto->exists ? 'Editar producto' : 'Nuevo producto'">
        <a href="{{ route('productos.index') }}" class="btn-secondary">← Volver</a>
    </x-page-header>

    <div class="card max-w-3xl">
        <x-val-errors />
        <form method="POST" action="{{ $producto->exists ? route('productos.update', $producto) : route('productos.store') }}" class="space-y-5">
            @csrf
            @if($producto->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre *</label>
                    <input name="nombre" value="{{ old('nombre', $producto->nombre) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Categoría *</label>
                    <select name="categoria_id" class="form-input-c" required>
                        <option value="">Selecciona...</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id }}" @selected(old('categoria_id', $producto->categoria_id)==$c->id)>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Precio de venta *</label>
                    <input type="number" step="0.01" name="precio" value="{{ old('precio', $producto->precio) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Costo</label>
                    <input type="number" step="0.01" name="costo" value="{{ old('costo', $producto->costo) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">SKU / Código</label>
                    <input name="sku" value="{{ old('sku', $producto->sku) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Stock</label>
                    <input type="number" name="stock" value="{{ old('stock', $producto->stock ?? 0) }}" class="form-input-c">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Descripción</label>
                    <textarea name="descripcion" rows="2" class="form-input-c">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="disponible" value="1" @checked(old('disponible', $producto->disponible ?? true)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                    Disponible en la carta
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="controla_stock" value="1" @checked(old('controla_stock', $producto->controla_stock ?? false)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                    Controlar stock
                </label>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('productos.index') }}" class="btn-secondary">Cancelar</a>
                <button class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</x-app-layout>

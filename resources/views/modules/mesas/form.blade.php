<x-app-layout :title="$mesa->exists ? 'Editar mesa' : 'Nueva mesa'">
    <x-page-header :title="$mesa->exists ? 'Editar mesa' : 'Nueva mesa'">
        <a href="{{ route('mesas.index') }}" class="btn-secondary">&larr; Volver</a>
    </x-page-header>

    <div class="card max-w-2xl">
        <x-val-errors />
        <form method="POST" action="{{ $mesa->exists ? route('mesas.update', $mesa) : route('mesas.store') }}" class="space-y-5">
            @csrf
            @if($mesa->exists) @method('PUT') @endif
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Numero *</label>
                    <input name="numero" value="{{ old('numero', $mesa->numero) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre</label>
                    <input name="nombre" value="{{ old('nombre', $mesa->nombre) }}" class="form-input-c" placeholder="Mesa VIP">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Capacidad *</label>
                    <input type="number" name="capacidad" value="{{ old('capacidad', $mesa->capacidad ?? 4) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Zona *</label>
                    <input name="zona" value="{{ old('zona', $mesa->zona ?? 'Salon principal') }}" class="form-input-c" list="zonas" required>
                    <datalist id="zonas"><option>Salon principal</option><option>Terraza</option><option>Privados</option><option>Barra</option></datalist>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Estado *</label>
                    <select name="estado" class="form-input-c" required>
                        @foreach (['libre'=>'Libre','ocupada'=>'Ocupada','reservada'=>'Reservada','cuenta'=>'Por cobrar'] as $k=>$v)
                            <option value="{{ $k }}" @selected(old('estado', $mesa->estado)==$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('mesas.index') }}" class="btn-secondary">Cancelar</a>
                <button class="btn-primary">Guardar</button>
            </div>
        </form>

        @if($mesa->exists)
            <form method="POST" action="{{ route('mesas.destroy', $mesa) }}" onsubmit="return confirm('Eliminar mesa?')" class="mt-4 border-t border-slate-100 pt-4">
                @csrf @method('DELETE')
                <button class="text-sm font-semibold text-accent-600 hover:text-accent-700">Eliminar esta mesa</button>
            </form>
        @endif
    </div>
</x-app-layout>

<x-app-layout :title="$cliente->exists ? 'Editar cliente' : 'Nuevo cliente'">
    <x-page-header :title="$cliente->exists ? 'Editar cliente' : 'Nuevo cliente'">
        <a href="{{ route('clientes.index') }}" class="btn-secondary">← Volver</a>
    </x-page-header>

    <div class="card max-w-2xl">
        <x-val-errors />
        <form method="POST" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}" class="space-y-5">
            @csrf
            @if($cliente->exists) @method('PUT') @endif
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre *</label>
                    <input name="nombre" value="{{ old('nombre', $cliente->nombre) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Documento (DNI/RUC)</label>
                    <input name="documento" value="{{ old('documento', $cliente->documento) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono', $cliente->telefono) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $cliente->email) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Puntos</label>
                    <input type="number" name="puntos" value="{{ old('puntos', $cliente->puntos ?? 0) }}" class="form-input-c">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Dirección</label>
                    <input name="direccion" value="{{ old('direccion', $cliente->direccion) }}" class="form-input-c">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('clientes.index') }}" class="btn-secondary">Cancelar</a>
                <button class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>

    @if($cliente->exists)
        @php $movs = $cliente->movimientosPuntos()->latest()->take(12)->get(); @endphp
        <div class="card mt-4 max-w-2xl">
            <div class="mb-3 flex items-center justify-between">
                <p class="text-sm font-bold text-slate-700">Historial de puntos</p>
                <span class="badge bg-amber-50 text-amber-600">{{ $cliente->puntos }} pts disponibles</span>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-50">
                    @forelse($movs as $mv)
                        <tr>
                            <td class="py-2 text-slate-500">{{ $mv->created_at->format('d/m/y H:i') }}</td>
                            <td class="py-2 text-slate-600">{{ $mv->descripcion }}</td>
                            <td class="py-2 text-right font-semibold {{ $mv->tipo==='ganado' ? 'text-emerald-600' : 'text-accent-600' }}">{{ $mv->tipo==='ganado' ? '+' : '-' }}{{ $mv->puntos }} pts</td>
                        </tr>
                    @empty
                        <tr><td class="py-4 text-center text-slate-400">Sin movimientos de puntos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>

<x-app-layout title="Inventario">
    @php $m = \App\Models\Restaurante::actual()->moneda; @endphp
    <x-page-header title="Inventario" subtitle="Control de insumos y stock.">
        <a href="{{ route('insumos.create') }}" class="btn-primary">+ Nuevo insumo</a>
    </x-page-header>

    @if($bajoStock > 0)
        <div class="mb-4 rounded-xl bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700">
            ⚠️ Tienes {{ $bajoStock }} insumo(s) en o por debajo del stock mínimo.
        </div>
    @endif

    <form method="GET" class="card mb-4 flex gap-3">
        <input name="q" value="{{ request('q') }}" placeholder="Buscar insumo..." class="form-input-c max-w-md">
        <button class="btn-secondary">Buscar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                    <th class="py-2 font-semibold">Insumo</th>
                    <th class="py-2 font-semibold">Stock</th>
                    <th class="py-2 font-semibold">Mínimo</th>
                    <th class="py-2 font-semibold">Costo unit.</th>
                    <th class="py-2 font-semibold">Proveedor</th>
                    <th class="py-2 text-right font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($insumos as $ins)
                    <tr>
                        <td class="py-3 font-semibold text-slate-700">{{ $ins->nombre }}</td>
                        <td class="py-3">
                            <span class="badge {{ $ins->bajo_stock ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                                {{ rtrim(rtrim(number_format($ins->stock,2),'0'),'.') }} {{ $ins->unidad }}
                            </span>
                        </td>
                        <td class="py-3 text-slate-600">{{ rtrim(rtrim(number_format($ins->stock_minimo,2),'0'),'.') }}</td>
                        <td class="py-3 text-slate-600">{{ $m }} {{ number_format($ins->costo,2) }}</td>
                        <td class="py-3 text-slate-600">{{ $ins->proveedor ?: '—' }}</td>
                        <td class="py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('insumos.edit', $ins) }}" class="btn-secondary py-1 text-xs">Editar</a>
                                <form method="POST" action="{{ route('insumos.destroy', $ins) }}" onsubmit="return confirm('¿Eliminar insumo?')">
                                    @csrf @method('DELETE')<button class="btn-danger py-1 text-xs">×</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-slate-400">No hay insumos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $insumos->links() }}</div>
</x-app-layout>

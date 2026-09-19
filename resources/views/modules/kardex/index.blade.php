<x-app-layout title="Kardex">
    <x-page-header title="Kardex de inventario" subtitle="Entradas y salidas de insumos." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Registrar movimiento --}}
        <div class="lg:col-span-1">
            <div class="card">
                <h3 class="mb-3 font-bold text-slate-800">Registrar movimiento</h3>
                <form method="POST" action="{{ route('kardex.store') }}" class="space-y-3" x-data="{ tipo: 'entrada' }">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Insumo</label>
                        <select name="insumo_id" class="form-input-c" required>
                            <option value="">Selecciona…</option>
                            @foreach($insumos as $i)
                                <option value="{{ $i->id }}" @selected(request('insumo_id')==$i->id)>{{ $i->nombre }} ({{ $i->unidad }}) · stock {{ number_format($i->stock,2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Tipo</label>
                        <select name="tipo" x-model="tipo" class="form-input-c">
                            <option value="entrada">Entrada (compra / ingreso)</option>
                            <option value="ajuste">Ajuste (fijar stock exacto)</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500"><span x-text="tipo==='ajuste' ? 'Nuevo stock total' : 'Cantidad a ingresar'"></span></label>
                        <input type="number" step="0.001" min="0" name="cantidad" class="form-input-c" required placeholder="0">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Motivo (opcional)</label>
                        <input name="motivo" class="form-input-c" placeholder="Ej. compra a proveedor">
                    </div>
                    <button class="btn-primary w-full">Registrar</button>
                </form>
            </div>
        </div>

        {{-- Historial --}}
        <div class="lg:col-span-2">
            <form method="GET" class="card mb-4 flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">Insumo</label>
                    <select name="insumo_id" class="form-input-c">
                        <option value="">Todos</option>
                        @foreach($insumos as $i)<option value="{{ $i->id }}" @selected(request('insumo_id')==$i->id)>{{ $i->nombre }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">Tipo</label>
                    <select name="tipo" class="form-input-c">
                        <option value="">Todos</option>
                        @foreach(['entrada'=>'Entrada','salida'=>'Salida','ajuste'=>'Ajuste'] as $k=>$v)<option value="{{ $k }}" @selected(request('tipo')==$k)>{{ $v }}</option>@endforeach
                    </select>
                </div>
                <button class="btn-secondary">Filtrar</button>
            </form>

            <div class="card overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 font-semibold">Fecha</th><th class="py-2 font-semibold">Insumo</th><th class="py-2 font-semibold">Tipo</th>
                        <th class="py-2 text-right font-semibold">Cantidad</th><th class="py-2 text-right font-semibold">Stock</th><th class="py-2 font-semibold">Motivo</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($movimientos as $mov)
                            <tr>
                                <td class="py-2.5 text-slate-500">{{ $mov->created_at->format('d/m H:i') }}</td>
                                <td class="py-2.5 font-semibold text-slate-700">{{ $mov->insumo?->nombre ?? '—' }}</td>
                                <td class="py-2.5">
                                    @php $colores = ['entrada'=>'emerald','salida'=>'accent','ajuste'=>'amber']; $c=$colores[$mov->tipo]??'slate'; @endphp
                                    <span class="badge bg-{{ $c }}-50 text-{{ $c }}-600">{{ ucfirst($mov->tipo) }}</span>
                                </td>
                                <td class="py-2.5 text-right font-semibold {{ $mov->tipo==='salida' ? 'text-accent-600' : 'text-emerald-600' }}">
                                    {{ $mov->tipo==='salida' ? '−' : '+' }}{{ number_format($mov->cantidad,3) }}
                                </td>
                                <td class="py-2.5 text-right text-slate-500">{{ number_format($mov->stock_anterior,2) }} → <span class="font-semibold text-slate-700">{{ number_format($mov->stock_nuevo,2) }}</span></td>
                                <td class="py-2.5 text-slate-500">{{ $mov->motivo }}{{ $mov->referencia ? ' · '.$mov->referencia : '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-slate-400">Sin movimientos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $movimientos->links() }}</div>
        </div>
    </div>
</x-app-layout>

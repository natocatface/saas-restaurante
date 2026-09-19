<x-app-layout title="Reportes">
    @php $m = $config->moneda; @endphp
    <x-page-header title="Reportes de ventas" subtitle="Analiza el desempeño de tu restaurante.">
        <a href="{{ route('reportes.exportar', request()->only('desde','hasta')) }}" class="btn-secondary">⬇️ Excel (CSV)</a>
        <a href="{{ route('reportes.imprimir', request()->only('desde','hasta')) }}" target="_blank" class="btn-primary">🖨️ PDF</a>
    </x-page-header>

    <form method="GET" class="card mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-semibold text-slate-500">Desde</label>
            <input type="date" name="desde" value="{{ \Illuminate\Support\Carbon::parse($desde)->toDateString() }}" class="form-input-c">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-slate-500">Hasta</label>
            <input type="date" name="hasta" value="{{ \Illuminate\Support\Carbon::parse($hasta)->toDateString() }}" class="form-input-c">
        </div>
        <button class="btn-primary">Aplicar</button>
    </form>

    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card"><p class="text-xs uppercase text-slate-400">Ventas totales</p><p class="mt-2 text-2xl font-extrabold text-slate-800">{{ $m }} {{ number_format($totalVentas,2) }}</p></div>
        <div class="card"><p class="text-xs uppercase text-slate-400">N° de pedidos</p><p class="mt-2 text-2xl font-extrabold text-slate-800">{{ $numPedidos }}</p></div>
        <div class="card"><p class="text-xs uppercase text-slate-400">Ticket promedio</p><p class="mt-2 text-2xl font-extrabold text-slate-800">{{ $m }} {{ number_format($ticketPromedio,2) }}</p></div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <p class="mb-3 text-sm font-bold text-slate-700">Ventas por día</p>
            <div id="chartVentas"></div>
        </div>
        <div class="card">
            <p class="mb-3 text-sm font-bold text-slate-700">Por método de pago</p>
            <div id="chartMetodo"></div>
        </div>
    </div>

    <div class="card mt-4">
        <p class="mb-4 text-sm font-bold text-slate-700">Top 10 productos</p>
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-100 text-left text-xs uppercase text-slate-400">
                <th class="py-2 font-semibold">#</th><th class="py-2 font-semibold">Producto</th>
                <th class="py-2 text-center font-semibold">Unidades</th><th class="py-2 text-right font-semibold">Ingresos</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($topProductos as $i => $tp)
                    <tr>
                        <td class="py-2.5 text-slate-400">{{ $i+1 }}</td>
                        <td class="py-2.5 font-semibold text-slate-700">{{ $tp->nombre_producto }}</td>
                        <td class="py-2.5 text-center text-slate-600">{{ $tp->q }}</td>
                        <td class="py-2.5 text-right font-bold text-slate-700">{{ $m }} {{ number_format($tp->t,2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-slate-400">Sin datos en el rango seleccionado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Rentabilidad por plato --}}
    <div class="card mt-4">
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm font-bold text-slate-700">Rentabilidad por plato</p>
            <span class="text-xs text-slate-400">Costo según receta / costo del producto</span>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Ingresos</p><p class="font-bold text-slate-800">{{ $m }} {{ number_format($ingresoTotalR,2) }}</p></div>
            <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Costo</p><p class="font-bold text-accent-600">{{ $m }} {{ number_format($costoTotalR,2) }}</p></div>
            <div class="rounded-xl bg-emerald-50 p-3"><p class="text-xs text-emerald-600">Ganancia</p><p class="font-bold text-emerald-700">{{ $m }} {{ number_format($gananciaTotalR,2) }}</p></div>
            <div class="rounded-xl bg-brand-50 p-3"><p class="text-xs text-brand-600">Margen</p><p class="font-bold text-brand-700">{{ $margenTotalR }}%</p></div>
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                    <th class="py-2 font-semibold">Producto</th><th class="py-2 text-center font-semibold">Unid.</th>
                    <th class="py-2 text-right font-semibold">Ingresos</th><th class="py-2 text-right font-semibold">Costo</th>
                    <th class="py-2 text-right font-semibold">Ganancia</th><th class="py-2 text-right font-semibold">Margen</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($rentabilidad as $r)
                        <tr>
                            <td class="py-2.5 font-semibold text-slate-700">{{ $r->nombre }}</td>
                            <td class="py-2.5 text-center text-slate-600">{{ $r->unidades }}</td>
                            <td class="py-2.5 text-right text-slate-600">{{ $m }} {{ number_format($r->ingreso,2) }}</td>
                            <td class="py-2.5 text-right text-slate-500">{{ $m }} {{ number_format($r->costo_total,2) }}</td>
                            <td class="py-2.5 text-right font-semibold text-emerald-600">{{ $m }} {{ number_format($r->ganancia,2) }}</td>
                            <td class="py-2.5 text-right">
                                <span class="badge {{ $r->margen >= 50 ? 'bg-emerald-50 text-emerald-600' : ($r->margen >= 20 ? 'bg-amber-50 text-amber-600' : 'bg-accent-50 text-accent-600') }}">{{ $r->margen }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-slate-400">Sin datos. Define costos/recetas y registra ventas en el rango.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new ApexCharts(document.querySelector('#chartVentas'), {
                chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Ventas', data: @json($porDia->pluck('t')->map(fn($v)=>round($v,2))) }],
                xaxis: { categories: @json($porDia->pluck('d')->map(fn($d)=>\Illuminate\Support\Carbon::parse($d)->format('d/m'))), labels: { style: { colors: '#94a3b8' } } },
                yaxis: { labels: { style: { colors: '#94a3b8' } } },
                colors: ['#ea580c'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            }).render();

            new ApexCharts(document.querySelector('#chartMetodo'), {
                chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
                series: @json($porMetodo->pluck('t')->map(fn($v)=>round($v,2))),
                labels: @json($porMetodo->pluck('metodo_pago')->map(fn($x)=>ucfirst($x ?? 'N/D'))),
                colors: ['#ea580c', '#f59e0b', '#22c55e', '#ef4444', '#fb923c'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true },
            }).render();
        });
    </script>
    @endpush
</x-app-layout>

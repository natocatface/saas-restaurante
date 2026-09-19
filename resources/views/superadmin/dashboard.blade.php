<x-superadmin-layout title="Dashboard">
    <h1 class="mb-6 text-2xl font-extrabold text-slate-800">Resumen del SaaS</h1>

    {{-- KPIs en degradado --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $kpis = [
                ['Restaurantes', $total, 'from-brand-500 to-brand-700', 'shadow-brand-600/30', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['Activos', $activos, 'from-emerald-500 to-emerald-700', 'shadow-emerald-600/30', 'M5 13l4 4L19 7'],
                ['En prueba', $trial, 'from-amber-400 to-orange-600', 'shadow-orange-500/30', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Suspendidos', $suspendidos, 'from-accent-500 to-accent-700', 'shadow-accent-600/30', 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
            ];
        @endphp
        @foreach ($kpis as [$label, $val, $grad, $shadow, $icon])
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $grad }} p-5 text-white shadow-lg {{ $shadow }}">
                <div class="absolute -right-8 -top-10 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/80">{{ $label }}</p>
                        <p class="mt-2 text-3xl font-extrabold">{{ $val }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Tarjetas financieras --}}
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card flex items-center justify-between">
            <div><p class="text-xs font-semibold uppercase text-slate-400">MRR estimado</p><p class="mt-1 text-2xl font-extrabold text-brand-600">S/ {{ number_format($mrr, 2) }}</p><p class="text-xs text-slate-400">Ingreso recurrente mensual</p></div>
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></span>
        </div>
        <div class="card flex items-center justify-between">
            <div><p class="text-xs font-semibold uppercase text-slate-400">Ingreso histórico</p><p class="mt-1 text-2xl font-extrabold text-slate-800">S/ {{ number_format($ingresoTotal, 2) }}</p><p class="text-xs text-slate-400">Suscripciones pagadas</p></div>
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v-1m0-8a9 9 0 110 18 9 9 0 010-18z"/></svg></span>
        </div>
        <div class="card flex items-center justify-between">
            <div><p class="text-xs font-semibold uppercase text-slate-400">Usuarios totales</p><p class="mt-1 text-2xl font-extrabold text-slate-800">{{ $totalUsuarios }}</p><p class="text-xs text-slate-400">Personal de todos los restaurantes</p></div>
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/></svg></span>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <p class="mb-2 text-sm font-bold text-slate-700">Crecimiento de restaurantes</p>
            <div id="chartCrecimiento"></div>
        </div>
        <div class="card">
            <p class="mb-2 text-sm font-bold text-slate-700">Distribución por plan</p>
            <div id="chartPlanes"></div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <p class="mb-2 text-sm font-bold text-slate-700">Ingresos por mes</p>
            <div id="chartIngresos"></div>
        </div>
        <div class="card">
            <p class="mb-4 text-sm font-bold text-slate-700">Restaurantes por plan</p>
            <div class="space-y-3">
                @foreach ($porPlan as $p)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-600">{{ $p->nombre }} <span class="text-xs text-slate-400">S/ {{ number_format($p->precio,0) }}</span></span>
                        <span class="badge bg-brand-50 text-brand-600">{{ $p->restaurantes_count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recientes --}}
    <div class="card mt-4">
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm font-bold text-slate-700">Restaurantes recientes</p>
            <a href="{{ route('superadmin.restaurantes.index') }}" class="text-xs font-semibold text-brand-600">Ver todos →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-slate-100 text-left text-xs uppercase text-slate-400">
                    <th class="py-2 font-semibold">Restaurante</th><th class="py-2 font-semibold">Plan</th>
                    <th class="py-2 font-semibold">Estado</th><th class="py-2 font-semibold">Alta</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($recientes as $r)
                        <tr>
                            <td class="py-2.5"><a href="{{ route('superadmin.restaurantes.show', $r) }}" class="font-semibold text-slate-700 hover:text-brand-600">{{ $r->nombre }}</a></td>
                            <td class="py-2.5 text-slate-600">{{ $r->plan?->nombre ?? '—' }}</td>
                            <td class="py-2.5"><span class="badge bg-{{ $r->estadoColor() }}-50 text-{{ $r->estadoColor() }}-600">{{ $r->estadoLabel() }}</span></td>
                            <td class="py-2.5 text-slate-500">{{ $r->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-slate-400">Aún no hay restaurantes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const naranja = '#ea580c', ambar = '#f59e0b', rojo = '#ef4444', verde = '#10b981';

            new ApexCharts(document.querySelector('#chartCrecimiento'), {
                chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Altas', data: @json($altasData) }],
                xaxis: { categories: @json($mesLabels), labels: { style: { colors: '#94a3b8' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: (v) => Math.round(v) } },
                colors: [naranja],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            }).render();

            new ApexCharts(document.querySelector('#chartPlanes'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: @json($planData),
                labels: @json($planLabels),
                colors: [naranja, ambar, rojo, '#fb923c', '#fdba74'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: (v) => Math.round(v) + '%' },
                plotOptions: { pie: { donut: { size: '62%' } } },
            }).render();

            new ApexCharts(document.querySelector('#chartIngresos'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Ingresos', data: @json($ingresosData) }],
                xaxis: { categories: @json($mesLabels), labels: { style: { colors: '#94a3b8' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: (v) => 'S/ ' + Math.round(v) } },
                colors: [ambar],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                tooltip: { y: { formatter: (v) => 'S/ ' + Number(v).toLocaleString('es-PE', {minimumFractionDigits: 2}) } },
            }).render();
        });
    </script>
    @endpush
</x-superadmin-layout>

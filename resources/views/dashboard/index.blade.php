<x-app-layout title="Dashboard">
    @php $m = $config->moneda; @endphp

    {{-- Encabezado --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Bienvenido de vuelta, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
            <p class="mt-1 text-sm text-slate-500 capitalize">{{ \Illuminate\Support\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
        </div>
        <a href="{{ route('pos.index') }}" class="btn-primary">+ Nueva venta</a>
    </div>

    @php
        $ocupacionPct = $mesasTotal > 0 ? (int) round(min(100, $mesasOcupadas / $mesasTotal * 100)) : 0;
        $ring = fn ($pct) => 138.23 - 138.23 * min(100, max(0, (int) $pct)) / 100;
    @endphp

    {{-- Tarjetas de métricas --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Ventas del día --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 p-5 text-white shadow-lg shadow-brand-600/30">
            <div class="absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Ventas del día</p>
                    </div>
                    <p class="truncate text-2xl font-extrabold sm:text-3xl">{{ $m }} {{ number_format($ventasHoy, 2) }}</p>
                    <p class="mt-1 text-xs text-white/70">Pedidos pagados hoy</p>
                </div>
                <div class="relative flex h-16 w-16 shrink-0 items-center justify-center">
                    <svg class="h-16 w-16 -rotate-90" viewBox="0 0 56 56">
                        <circle cx="28" cy="28" r="22" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="6"/>
                        <circle cx="28" cy="28" r="22" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-dasharray="138.23" stroke-dashoffset="{{ $ring($ingresoPct) }}"/>
                    </svg>
                    <span class="absolute text-xs font-bold">{{ (int) $ingresoPct }}%</span>
                </div>
            </div>
        </div>

        {{-- Mesas ocupadas --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 to-orange-600 p-5 text-white shadow-lg shadow-orange-500/30">
            <div class="absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        </span>
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Mesas ocupadas</p>
                    </div>
                    <p class="text-2xl font-extrabold sm:text-3xl">{{ $mesasOcupadas }}<span class="text-base font-semibold text-white/60">/{{ $mesasTotal }}</span></p>
                    <p class="mt-1 text-xs text-white/70">En servicio ahora</p>
                </div>
                <div class="relative flex h-16 w-16 shrink-0 items-center justify-center">
                    <svg class="h-16 w-16 -rotate-90" viewBox="0 0 56 56">
                        <circle cx="28" cy="28" r="22" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="6"/>
                        <circle cx="28" cy="28" r="22" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-dasharray="138.23" stroke-dashoffset="{{ $ring($ocupacionPct) }}"/>
                    </svg>
                    <span class="absolute text-xs font-bold">{{ $ocupacionPct }}%</span>
                </div>
            </div>
        </div>

        {{-- Pedidos activos --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 to-accent-500 p-5 text-white shadow-lg shadow-accent-500/30">
            <div class="absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        </span>
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Pedidos activos</p>
                    </div>
                    <p class="text-2xl font-extrabold sm:text-3xl">{{ $pedidosActivos }}</p>
                    <p class="mt-1 text-xs text-white/70">Pendiente · Preparando · Servido</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
            </div>
        </div>

        {{-- Clientes nuevos --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-accent-500 to-accent-700 p-5 text-white shadow-lg shadow-accent-600/30">
            <div class="absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </span>
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Clientes nuevos</p>
                    </div>
                    <p class="text-2xl font-extrabold sm:text-3xl">{{ $clientesNuevos }}</p>
                    <p class="mt-1 text-xs text-white/70">Registrados hoy</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-3-6.7"/></svg>
                </span>
            </div>
        </div>
    </div>

    {{-- Progreso meta mensual --}}
    <div class="card mt-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-sm font-bold text-slate-700">Progreso de meta mensual</p>
                <p class="text-xs text-slate-400">{{ $m }} {{ number_format($ventasMes, 2) }} de {{ $m }} {{ number_format($meta, 2) }}</p>
            </div>
            <span class="badge bg-brand-50 text-brand-700">{{ $progresoMeta }}%</span>
        </div>
        <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-accent-500" style="width: {{ $progresoMeta }}%"></div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

        {{-- 1. Actividad por hora --}}
        <div class="card">
            <div class="mb-2 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-700">Actividad por hora</p>
                    <p class="text-xs text-slate-400">Ventas de hoy por franja horaria</p>
                </div>
                <span class="badge bg-brand-50 text-brand-700">Hoy</span>
            </div>
            <div id="chartActividad" class="w-full"></div>
        </div>

        {{-- 2. Ventas últimos 7 días --}}
        <div class="card">
            <div class="mb-2 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-700">Ventas últimos 7 días</p>
                    <p class="text-xs text-slate-400">Tendencia de ingresos diarios</p>
                </div>
                <span class="badge bg-emerald-50 text-emerald-600">7 días</span>
            </div>
            <div id="chartSemana" class="w-full"></div>
        </div>

        {{-- 3. Avance de meta diaria --}}
        <div class="card">
            <div class="mb-2 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-700">Avance de meta diaria</p>
                    <p class="text-xs text-slate-400">{{ $m }} {{ number_format($ventasHoy, 2) }} vendido hoy</p>
                </div>
                <span class="badge bg-indigo-50 text-indigo-600">Meta</span>
            </div>
            <div id="chartGauge" class="w-full"></div>
        </div>

        {{-- 4. Distribución de mesas --}}
        <div class="card">
            <div class="mb-2 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-700">Distribución de mesas</p>
                    <p class="text-xs text-slate-400">Estado actual de los salones</p>
                </div>
                <span class="badge bg-amber-50 text-amber-600">{{ $mesasTotal }} mesas</span>
            </div>
            <div id="chartMesas" class="w-full"></div>
        </div>
    </div>

    {{-- Salones + Más vendidos --}}
    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm font-bold text-slate-700">Estado de salones</p>
                <a href="{{ route('mesas.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Ver todas →</a>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @forelse ($mesas as $mesa)
                    @php
                        $c = $mesa->color_estado;
                        $labels = ['libre'=>'Libre','ocupada'=>'Ocupada','reservada'=>'Reservada','cuenta'=>'Por cobrar'];
                    @endphp
                    <div class="rounded-2xl border border-{{ $c }}-100 bg-{{ $c }}-50 p-4 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $c }}-100 text-{{ $c }}-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16"/></svg>
                        </div>
                        <p class="mt-2 text-sm font-bold text-slate-700">{{ $mesa->nombre ?? 'Mesa '.$mesa->numero }}</p>
                        <p class="text-[11px] font-semibold text-{{ $c }}-600">{{ $labels[$mesa->estado] ?? $mesa->estado }}</p>
                    </div>
                @empty
                    <p class="col-span-full text-sm text-slate-400">No hay mesas registradas.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <p class="mb-4 text-sm font-bold text-slate-700">Más vendidos</p>
            <div class="space-y-3">
                @forelse ($masVendidos as $i => $prod)
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-xs font-bold text-brand-600">{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-700">{{ $prod->nombre_producto }}</p>
                            <p class="text-xs text-slate-400">{{ $prod->total }} unidades</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Aún no hay ventas registradas.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Pedidos recientes --}}
    <div class="card mt-4">
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm font-bold text-slate-700">Pedidos recientes</p>
            <a href="{{ route('pedidos.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Ver todos →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="pb-2 font-semibold">Código</th>
                        <th class="pb-2 font-semibold">Mesa</th>
                        <th class="pb-2 font-semibold">Atendido por</th>
                        <th class="pb-2 font-semibold">Estado</th>
                        <th class="pb-2 text-right font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($pedidosRecientes as $p)
                        <tr>
                            <td class="py-2.5 font-mono text-xs font-semibold text-slate-600">{{ $p->codigo }}</td>
                            <td class="py-2.5 text-slate-600">{{ $p->mesa?->nombre ?? '—' }}</td>
                            <td class="py-2.5 text-slate-600">{{ $p->user?->name ?? '—' }}</td>
                            <td class="py-2.5">
                                <span class="badge bg-{{ $p->color_estado }}-50 text-{{ $p->color_estado }}-600">{{ ucfirst($p->estado) }}</span>
                            </td>
                            <td class="py-2.5 text-right font-bold text-slate-700">{{ $m }} {{ number_format($p->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-slate-400">No hay pedidos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fmt = (v) => '{{ $m }} ' + Number(v).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            const fmtK = (v) => v >= 1000 ? (v / 1000).toFixed(1).replace('.0', '') + 'k' : Math.round(v);
            const baseAxis = { axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { colors: '#94a3b8', fontSize: '11px' } } };
            const smallScreen = [{ breakpoint: 640, options: { chart: { height: 240 } } }];

            /* 1 ── Actividad por hora (área con degradado) */
            new ApexCharts(document.querySelector('#chartActividad'), {
                chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit', parentHeightOffset: 0, sparkline: { enabled: false } },
                series: [{ name: 'Ventas', data: @json($serie) }],
                xaxis: { categories: @json($horas), ...baseAxis, tickAmount: 6 },
                yaxis: { min: 0, forceNiceScale: true, tickAmount: 4, labels: { style: { colors: '#94a3b8' }, formatter: fmtK } },
                colors: ['#f97316'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.02, stops: [0, 100] } },
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                markers: { size: 0, hover: { size: 5 } },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4, padding: { left: 8, right: 8 } },
                tooltip: { y: { formatter: fmt } },
                responsive: smallScreen,
            }).render();

            /* 2 ── Ventas últimos 7 días (columnas con degradado) */
            new ApexCharts(document.querySelector('#chartSemana'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit', parentHeightOffset: 0 },
                series: [{ name: 'Ventas', data: @json($ultimos7Data) }],
                xaxis: { categories: @json($ultimos7Labels), ...baseAxis },
                yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: fmtK } },
                plotOptions: { bar: { borderRadius: 8, borderRadiusApplication: 'end', columnWidth: '48%', distributed: false } },
                colors: ['#10b981'],
                fill: { type: 'gradient', gradient: { shade: 'light', type: 'vertical', gradientToColors: ['#6ee7b7'], opacityFrom: 1, opacityTo: 0.85, stops: [0, 100] } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4, padding: { left: 8, right: 8 } },
                tooltip: { y: { formatter: fmt } },
                responsive: smallScreen,
            }).render();

            /* 3 ── Avance de meta diaria (gauge radial) */
            (function () {
                const gaugeVal = {{ $ingresoPct }};
                // Usar al menos 0.1 para que ApexCharts dibuje siempre el track
                const gaugeDisplay = gaugeVal <= 0 ? 0.1 : gaugeVal;
                const gaugeColor = gaugeVal >= 80 ? '#10b981' : gaugeVal >= 50 ? '#6366f1' : gaugeVal >= 20 ? '#f59e0b' : '#ef4444';
                const gaugeGradient = gaugeVal >= 80 ? '#34d399' : gaugeVal >= 50 ? '#a855f7' : gaugeVal >= 20 ? '#fb923c' : '#f87171';

                new ApexCharts(document.querySelector('#chartGauge'), {
                    chart: {
                        type: 'radialBar', height: 280, fontFamily: 'inherit',
                        parentHeightOffset: 0,
                        animations: { enabled: true, speed: 800, animateGradually: { enabled: true, delay: 150 } }
                    },
                    series: [gaugeDisplay],
                    colors: [gaugeColor],
                    plotOptions: { radialBar: {
                        startAngle: -135, endAngle: 135,
                        hollow: { size: '62%', background: 'transparent' },
                        track: {
                            background: '#eef2ff',
                            strokeWidth: '100%',
                            margin: 0,
                            dropShadow: { enabled: false }
                        },
                        dataLabels: {
                            name: {
                                show: true, color: '#94a3b8',
                                fontSize: '12px', fontWeight: 500,
                                offsetY: 28
                            },
                            value: {
                                show: true, color: '#1e293b',
                                fontSize: '32px', fontWeight: 700,
                                offsetY: -4,
                                formatter: () => gaugeVal + '%'
                            }
                        }
                    }},
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'dark', type: 'horizontal',
                            gradientToColors: [gaugeGradient],
                            stops: [0, 100]
                        }
                    },
                    stroke: { lineCap: 'round' },
                    labels: ['de la meta diaria'],
                    responsive: smallScreen,
                }).render();
            })();

            /* 4 ── Distribución de mesas (dona) */
            const mesasData = @json($mesasDonutData);
            new ApexCharts(document.querySelector('#chartMesas'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit', parentHeightOffset: 0 },
                series: mesasData,
                labels: @json($mesasDonutLabels),
                colors: ['#10b981', '#f97316', '#6366f1', '#ef4444'],
                legend: { position: 'bottom', fontSize: '12px', labels: { colors: '#64748b' }, markers: { radius: 12 } },
                stroke: { width: 2, colors: ['#fff'] },
                plotOptions: { pie: { donut: { size: '68%', labels: {
                    show: true,
                    value: { color: '#1e293b', fontSize: '26px', fontWeight: 700 },
                    total: { show: true, label: 'Total', color: '#94a3b8', fontSize: '13px', formatter: () => mesasData.reduce((a, b) => a + b, 0) }
                } } } },
                dataLabels: { enabled: true, formatter: (v) => Math.round(v) + '%', style: { fontSize: '11px', fontWeight: 600 }, dropShadow: { enabled: false } },
                tooltip: { y: { formatter: (v) => v + ' mesa(s)' } },
                responsive: [{ breakpoint: 640, options: { chart: { height: 260 }, legend: { position: 'bottom' } } }],
            }).render();
        });
    </script>
    @endpush
</x-app-layout>

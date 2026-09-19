<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de ventas · {{ $config->nombre }}</title>
    @vite(['resources/css/app.css'])
    <style>@media print { .no-print { display:none } } body { background:#fff }</style>
</head>
<body class="bg-white p-8 text-slate-700" onload="window.print()">
    @php $m = $config->moneda; @endphp

    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">{{ $config->nombre }}</h1>
                <p class="text-sm text-slate-500">Reporte de ventas y rentabilidad</p>
            </div>
            <div class="text-right text-sm text-slate-500">
                <p>{{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }} — {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }}</p>
                <p class="text-xs">Generado: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-3 gap-4">
            <div class="rounded-xl border border-slate-200 p-3"><p class="text-xs text-slate-400">Ventas totales</p><p class="text-xl font-extrabold text-slate-900">{{ $m }} {{ number_format($totalVentas,2) }}</p></div>
            <div class="rounded-xl border border-slate-200 p-3"><p class="text-xs text-slate-400">N° pedidos</p><p class="text-xl font-extrabold text-slate-900">{{ $numPedidos }}</p></div>
            <div class="rounded-xl border border-slate-200 p-3"><p class="text-xs text-slate-400">Ticket promedio</p><p class="text-xl font-extrabold text-slate-900">{{ $m }} {{ number_format($ticketPromedio,2) }}</p></div>
        </div>

        <h2 class="mb-2 text-sm font-bold uppercase tracking-wide text-slate-500">Rentabilidad por plato</h2>
        <table class="w-full text-sm">
            <thead><tr class="border-b-2 border-slate-300 text-left text-xs uppercase text-slate-500">
                <th class="py-2">Producto</th><th class="py-2 text-center">Unid.</th><th class="py-2 text-right">Ingresos</th>
                <th class="py-2 text-right">Costo</th><th class="py-2 text-right">Ganancia</th><th class="py-2 text-right">Margen</th>
            </tr></thead>
            <tbody>
                @forelse ($rentabilidad as $r)
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 font-semibold text-slate-700">{{ $r->nombre }}</td>
                        <td class="py-1.5 text-center">{{ $r->unidades }}</td>
                        <td class="py-1.5 text-right">{{ $m }} {{ number_format($r->ingreso,2) }}</td>
                        <td class="py-1.5 text-right text-slate-500">{{ $m }} {{ number_format($r->costo_total,2) }}</td>
                        <td class="py-1.5 text-right font-semibold">{{ $m }} {{ number_format($r->ganancia,2) }}</td>
                        <td class="py-1.5 text-right">{{ $r->margen }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-400">Sin datos.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-300 font-bold text-slate-800">
                    <td class="py-2">TOTAL</td><td></td>
                    <td class="py-2 text-right">{{ $m }} {{ number_format($ingresoTotalR,2) }}</td>
                    <td class="py-2 text-right">{{ $m }} {{ number_format($costoTotalR,2) }}</td>
                    <td class="py-2 text-right">{{ $m }} {{ number_format($gananciaTotalR,2) }}</td>
                    <td class="py-2 text-right">{{ $margenTotalR }}%</td>
                </tr>
            </tfoot>
        </table>

        <button onclick="window.print()" class="no-print mt-6 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Imprimir / Guardar PDF</button>
    </div>
</body>
</html>

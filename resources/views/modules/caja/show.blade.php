<x-app-layout title="Arqueo de caja">
    @php $m = fn($v) => 'S/ '.number_format((float)$v, 2); @endphp

    <x-page-header title="Arqueo de caja" subtitle="Cierre del {{ $caja->cerrada_at?->format('d/m/Y H:i') }}">
        <a href="{{ route('caja.index') }}" class="btn-secondary">← Volver</a>
        <button onclick="window.print()" class="btn-primary">🖨️ Imprimir</button>
    </x-page-header>

    <div class="mx-auto max-w-3xl space-y-6">
        {{-- Encabezado --}}
        <div class="card">
            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div><p class="text-xs text-slate-400">Abierta</p><p class="font-semibold text-slate-700">{{ $caja->abierta_at?->format('d/m/Y H:i') }}</p></div>
                <div><p class="text-xs text-slate-400">Cerrada</p><p class="font-semibold text-slate-700">{{ $caja->cerrada_at?->format('d/m/Y H:i') }}</p></div>
                <div><p class="text-xs text-slate-400">Abrió</p><p class="font-semibold text-slate-700">{{ $caja->usuario?->name ?? '—' }}</p></div>
                <div><p class="text-xs text-slate-400">Cerró</p><p class="font-semibold text-slate-700">{{ $caja->cerradaPor?->name ?? '—' }}</p></div>
            </div>
        </div>

        {{-- Resumen financiero --}}
        <div class="card">
            <h3 class="mb-4 font-bold text-slate-800">Resumen financiero</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Monto inicial</dt><dd class="font-semibold text-slate-800">{{ $m($caja->monto_inicial) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Ventas en efectivo</dt><dd class="font-semibold text-slate-800">{{ $m($resumen['ventas_efectivo']) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Ingresos extra</dt><dd class="font-semibold text-emerald-600">+{{ $m($resumen['ingresos']) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Egresos</dt><dd class="font-semibold text-accent-600">−{{ $m($resumen['egresos']) }}</dd></div>
                <div class="flex justify-between border-t border-slate-100 pt-2"><dt class="font-bold text-slate-700">Efectivo esperado</dt><dd class="font-extrabold text-slate-900">{{ $m($caja->efectivo_esperado) }}</dd></div>
                <div class="flex justify-between"><dt class="font-bold text-slate-700">Efectivo contado</dt><dd class="font-extrabold text-slate-900">{{ $m($caja->monto_contado) }}</dd></div>
                @php $dif = (float)$caja->diferencia; @endphp
                <div class="flex justify-between rounded-lg px-3 py-2 {{ $dif < 0 ? 'bg-accent-50' : ($dif > 0 ? 'bg-amber-50' : 'bg-emerald-50') }}">
                    <dt class="font-bold {{ $dif < 0 ? 'text-accent-700' : ($dif > 0 ? 'text-amber-700' : 'text-emerald-700') }}">Diferencia {{ $dif < 0 ? '(faltante)' : ($dif > 0 ? '(sobrante)' : '(cuadrada)') }}</dt>
                    <dd class="font-extrabold {{ $dif < 0 ? 'text-accent-700' : ($dif > 0 ? 'text-amber-700' : 'text-emerald-700') }}">{{ $m($caja->diferencia) }}</dd>
                </div>
            </dl>
        </div>

        {{-- Ventas por método --}}
        <div class="card">
            <h3 class="mb-3 font-bold text-slate-800">Ventas por método de pago</h3>
            @php $metodos = ['efectivo'=>'Efectivo','tarjeta'=>'Tarjeta','yape'=>'Yape','plin'=>'Plin','transferencia'=>'Transferencia']; @endphp
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach($metodos as $k=>$label)
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-400">{{ $label }}</p><p class="font-bold text-slate-800">{{ $m($resumen['ventas'][$k] ?? 0) }}</p></div>
                @endforeach
                <div class="rounded-xl bg-brand-50 p-3"><p class="text-xs text-brand-500">Total ventas</p><p class="font-bold text-brand-700">{{ $m($resumen['ventas_total']) }}</p></div>
            </div>
        </div>

        {{-- Movimientos --}}
        @if($caja->movimientos->count())
        <div class="card">
            <h3 class="mb-3 font-bold text-slate-800">Movimientos</h3>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-50">
                    @foreach($caja->movimientos->sortBy('created_at') as $mov)
                        <tr>
                            <td class="py-2 text-slate-500">{{ $mov->created_at->format('H:i') }}</td>
                            <td class="py-2 text-slate-700">{{ $mov->concepto }}</td>
                            <td class="py-2 text-right font-semibold {{ $mov->tipo==='ingreso' ? 'text-emerald-600' : 'text-accent-600' }}">{{ $mov->tipo==='ingreso'?'+':'−' }}{{ $m($mov->monto) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($caja->notas_cierre)
            <div class="card"><h3 class="mb-1 font-bold text-slate-800">Notas de cierre</h3><p class="text-sm text-slate-600">{{ $caja->notas_cierre }}</p></div>
        @endif
    </div>
</x-app-layout>

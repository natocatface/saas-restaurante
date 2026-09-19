<x-app-layout title="Caja">
    <x-page-header title="Caja" subtitle="Apertura, movimientos y arqueo de caja.">
        @if($caja)
            <span class="badge bg-emerald-50 text-emerald-700">● Caja abierta</span>
        @else
            <span class="badge bg-slate-100 text-slate-500">Caja cerrada</span>
        @endif
    </x-page-header>

    @php $m = fn($v) => 'S/ '.number_format((float)$v, 2); @endphp

    @if(! $caja)
        {{-- ====== ABRIR CAJA ====== --}}
        <div class="mx-auto max-w-lg">
            <div class="card">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-50 text-2xl">🔓</span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Abrir caja</h2>
                        <p class="text-sm text-slate-500">Registra el monto con el que inicias el turno.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('caja.abrir') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Monto inicial (efectivo)</label>
                        <input type="number" step="0.01" min="0" name="monto_inicial" value="{{ old('monto_inicial', '0.00') }}" required class="form-input-c" placeholder="0.00">
                        @error('monto_inicial') <p class="mt-1 text-sm text-accent-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Notas (opcional)</label>
                        <textarea name="notas_apertura" rows="2" class="form-input-c" placeholder="Ej. cambio inicial en monedas">{{ old('notas_apertura') }}</textarea>
                    </div>
                    <button class="btn-primary w-full py-3">Abrir caja</button>
                </form>
            </div>
        </div>
    @else
        {{-- ====== CAJA ABIERTA ====== --}}
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <span>Abierta por <strong>{{ $caja->usuario?->name ?? '—' }}</strong> · {{ $caja->abierta_at?->format('d/m/Y H:i') }} ({{ $caja->abierta_at?->diffForHumans() }})</span>
        </div>

        {{-- Tarjetas resumen --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            <div class="card"><p class="text-xs font-semibold uppercase text-slate-400">Monto inicial</p><p class="mt-1 text-2xl font-extrabold text-slate-900">{{ $m($caja->monto_inicial) }}</p></div>
            <div class="card"><p class="text-xs font-semibold uppercase text-slate-400">Ventas del turno</p><p class="mt-1 text-2xl font-extrabold text-slate-900">{{ $m($resumen['ventas_total']) }}</p><p class="text-xs text-slate-400">Efectivo: {{ $m($resumen['ventas_efectivo']) }}</p></div>
            <div class="card"><p class="text-xs font-semibold uppercase text-slate-400">Ingresos extra</p><p class="mt-1 text-2xl font-extrabold text-emerald-600">+{{ $m($resumen['ingresos']) }}</p></div>
            <div class="card"><p class="text-xs font-semibold uppercase text-slate-400">Egresos</p><p class="mt-1 text-2xl font-extrabold text-accent-600">−{{ $m($resumen['egresos']) }}</p></div>
            <div class="card col-span-2 bg-gradient-to-br from-brand-600 to-brand-800 text-white lg:col-span-2">
                <p class="text-xs font-semibold uppercase text-orange-100/80">Efectivo esperado en caja</p>
                <p class="mt-1 text-3xl font-extrabold">{{ $m($resumen['efectivo_esperado']) }}</p>
                <p class="text-xs text-orange-100/70">Inicial + ventas efectivo + ingresos − egresos</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Movimientos --}}
            <div class="lg:col-span-2">
                <div class="card">
                    <h3 class="mb-3 font-bold text-slate-800">Movimientos del turno</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                                <th class="py-2 font-semibold">Hora</th><th class="py-2 font-semibold">Concepto</th><th class="py-2 font-semibold">Tipo</th><th class="py-2 text-right font-semibold">Monto</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($caja->movimientos->sortByDesc('created_at') as $mov)
                                    <tr>
                                        <td class="py-2.5 text-slate-500">{{ $mov->created_at->format('H:i') }}</td>
                                        <td class="py-2.5 text-slate-700">{{ $mov->concepto }}<span class="block text-xs text-slate-400">{{ $mov->usuario?->name }} · {{ ucfirst($mov->metodo_pago) }}</span></td>
                                        <td class="py-2.5">@if($mov->tipo==='ingreso')<span class="badge bg-emerald-50 text-emerald-600">Ingreso</span>@else<span class="badge bg-accent-50 text-accent-600">Egreso</span>@endif</td>
                                        <td class="py-2.5 text-right font-semibold {{ $mov->tipo==='ingreso' ? 'text-emerald-600' : 'text-accent-600' }}">{{ $mov->tipo==='ingreso'?'+':'−' }}{{ $m($mov->monto) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="py-6 text-center text-slate-400">Sin movimientos manuales aún.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Ventas por método --}}
                <div class="card mt-6">
                    <h3 class="mb-3 font-bold text-slate-800">Ventas por método de pago</h3>
                    @php $metodos = ['efectivo'=>'Efectivo','tarjeta'=>'Tarjeta','yape'=>'Yape','plin'=>'Plin','transferencia'=>'Transferencia']; @endphp
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach($metodos as $k=>$label)
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs text-slate-400">{{ $label }}</p>
                                <p class="font-bold text-slate-800">{{ $m($resumen['ventas'][$k] ?? 0) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Acciones: movimiento + cierre --}}
            <div class="space-y-6" x-data="{ tab: 'mov' }">
                <div class="card">
                    <div class="mb-3 flex gap-2">
                        <button @click="tab='mov'" :class="tab==='mov' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600'" class="flex-1 rounded-lg py-2 text-sm font-semibold">Movimiento</button>
                        <button @click="tab='cierre'" :class="tab==='cierre' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600'" class="flex-1 rounded-lg py-2 text-sm font-semibold">Cerrar caja</button>
                    </div>

                    {{-- Form movimiento --}}
                    <form x-show="tab==='mov'" method="POST" action="{{ route('caja.movimiento') }}" class="space-y-3">
                        @csrf
                        <select name="tipo" class="form-input-c"><option value="ingreso">Ingreso (entra dinero)</option><option value="egreso">Egreso (sale dinero)</option></select>
                        <input name="concepto" required class="form-input-c" placeholder="Concepto (ej. compra de hielo)">
                        <input type="number" step="0.01" min="0.01" name="monto" required class="form-input-c" placeholder="Monto">
                        <select name="metodo_pago" class="form-input-c"><option value="efectivo">Efectivo</option><option value="tarjeta">Tarjeta</option><option value="yape">Yape</option><option value="plin">Plin</option><option value="transferencia">Transferencia</option></select>
                        <button class="btn-primary w-full">Registrar movimiento</button>
                    </form>

                    {{-- Form cierre --}}
                    <form x-show="tab==='cierre'" method="POST" action="{{ route('caja.cerrar') }}" class="space-y-3" onsubmit="return confirm('¿Cerrar la caja? Esta acción genera el arqueo y no se puede deshacer.')" style="display:none">
                        @csrf
                        <div class="rounded-lg bg-slate-50 p-3 text-sm">
                            <div class="flex justify-between"><span class="text-slate-500">Efectivo esperado</span><span class="font-bold text-slate-800">{{ $m($resumen['efectivo_esperado']) }}</span></div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Efectivo contado (real)</label>
                            <input type="number" step="0.01" min="0" name="monto_contado" required class="form-input-c" placeholder="0.00">
                        </div>
                        <textarea name="notas_cierre" rows="2" class="form-input-c" placeholder="Notas de cierre (opcional)"></textarea>
                        <button class="btn-danger w-full">Cerrar caja y ver arqueo</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ====== HISTORIAL ====== --}}
    @if($historial->count())
        <div class="card mt-8">
            <h3 class="mb-3 font-bold text-slate-800">Cierres recientes</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 font-semibold">Cierre</th><th class="py-2 font-semibold">Cajero</th><th class="py-2 text-right font-semibold">Esperado</th><th class="py-2 text-right font-semibold">Contado</th><th class="py-2 text-right font-semibold">Diferencia</th><th class="py-2 text-right font-semibold"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($historial as $h)
                            <tr>
                                <td class="py-2.5 text-slate-600">{{ $h->cerrada_at?->format('d/m/Y H:i') }}</td>
                                <td class="py-2.5 text-slate-600">{{ $h->usuario?->name ?? '—' }}</td>
                                <td class="py-2.5 text-right text-slate-600">{{ $m($h->efectivo_esperado) }}</td>
                                <td class="py-2.5 text-right text-slate-600">{{ $m($h->monto_contado) }}</td>
                                <td class="py-2.5 text-right font-semibold {{ (float)$h->diferencia < 0 ? 'text-accent-600' : ((float)$h->diferencia > 0 ? 'text-amber-600' : 'text-emerald-600') }}">{{ $m($h->diferencia) }}</td>
                                <td class="py-2.5 text-right"><a href="{{ route('caja.show', $h) }}" class="btn-secondary py-1 text-xs">Ver arqueo</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>

<x-app-layout title="Suscripción">
    <x-page-header title="Mi suscripción" subtitle="Gestiona el plan de tu restaurante." />

    {{-- Estado actual --}}
    <div class="card mb-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Plan actual</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ $restaurante->plan?->nombre ?? 'Sin plan' }}</p>
                <span class="badge bg-{{ $restaurante->estadoColor() }}-50 text-{{ $restaurante->estadoColor() }}-600 mt-2">{{ $restaurante->estadoLabel() }}</span>
            </div>
            <div class="text-right">
                @if ($restaurante->vigente())
                    <p class="text-sm text-slate-400">{{ $restaurante->estado === 'trial' ? 'Prueba termina en' : 'Renueva en' }}</p>
                    <p class="text-2xl font-extrabold text-{{ $restaurante->estadoColor() }}-600">{{ $restaurante->diasRestantes() }} días</p>
                @else
                    <p class="text-sm font-semibold text-rose-600">Tu suscripción no está activa</p>
                    <p class="text-xs text-slate-400">Elige un plan para reactivar el sistema.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Planes disponibles --}}
    <div x-data="{ open: false, plan: {}, metodo: 'tarjeta' }">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach ($planes as $plan)
                <div class="card flex flex-col {{ $restaurante->plan_id === $plan->id ? 'ring-2 ring-brand-500' : '' }} {{ $plan->destacado ? 'shadow-lg' : '' }}">
                    @if ($restaurante->plan_id === $plan->id)
                        <span class="badge bg-brand-50 text-brand-600 self-start">Plan actual</span>
                    @elseif ($plan->destacado)
                        <span class="badge bg-amber-50 text-amber-600 self-start">Recomendado</span>
                    @endif
                    <h3 class="mt-2 text-lg font-extrabold text-slate-800">{{ $plan->nombre }}</h3>
                    <p class="text-sm text-slate-500">{{ $plan->descripcion }}</p>
                    <p class="mt-3 text-3xl font-extrabold text-brand-600">S/ {{ number_format($plan->precio, 0) }}<span class="text-sm font-medium text-slate-400">/{{ $plan->intervalo === 'anual' ? 'año' : 'mes' }}</span></p>
                    <ul class="mt-4 space-y-1.5 text-sm text-slate-600">
                        <li>🪑 {{ $plan->limiteTexto('mesas') }} mesas</li>
                        <li>🍽️ {{ $plan->limiteTexto('productos') }} productos</li>
                        <li>👤 {{ $plan->limiteTexto('usuarios') }} usuarios</li>
                        @foreach (($plan->features ?? []) as $f)<li>✓ {{ $f }}</li>@endforeach
                    </ul>
                    <button type="button"
                            @click="open = true; plan = { id: {{ $plan->id }}, nombre: '{{ $plan->nombre }}', precio: '{{ number_format($plan->precio, 2) }}', intervalo: '{{ $plan->intervalo === 'anual' ? 'año' : 'mes' }}' }"
                            class="btn-primary mt-6 w-full justify-center py-2.5">
                        {{ $restaurante->plan_id === $plan->id ? 'Renovar' : 'Elegir plan' }}
                    </button>
                </div>
            @endforeach
        </div>

        {{-- Modal de pago simulado --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" style="display:none">
            <div @click.outside="open=false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-extrabold text-slate-800">Confirmar suscripción</h3>
                <p class="mt-1 text-sm text-slate-500">Plan <strong x-text="plan.nombre"></strong> — S/ <span x-text="plan.precio"></span>/<span x-text="plan.intervalo"></span></p>

                <form method="POST" action="{{ route('suscripcion.pagar') }}" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="plan_id" :value="plan.id">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Método de pago</label>
                        <select name="metodo_pago" x-model="metodo" class="form-input-c">
                            <option value="tarjeta">Tarjeta de crédito/débito</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                            <option value="transferencia">Transferencia bancaria</option>
                        </select>
                    </div>
                    <div class="rounded-xl bg-amber-50 px-4 py-3 text-xs text-amber-700">
                        ⚠️ Pago simulado para demostración. No se realizará ningún cargo real.
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open=false" class="btn-secondary">Cancelar</button>
                        <button type="submit" class="btn-primary">Pagar ahora</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Historial --}}
    <div class="card mt-6">
        <p class="mb-4 text-sm font-bold text-slate-700">Historial de pagos</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-slate-100 text-left text-xs uppercase text-slate-400">
                    <th class="py-2 font-semibold">Fecha</th><th class="py-2 font-semibold">Plan</th>
                    <th class="py-2 font-semibold">Periodo</th><th class="py-2 font-semibold">Método</th>
                    <th class="py-2 font-semibold">Referencia</th><th class="py-2 text-right font-semibold">Monto</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($historial as $s)
                        <tr>
                            <td class="py-2.5 text-slate-600">{{ $s->created_at->format('d/m/Y') }}</td>
                            <td class="py-2.5 font-semibold text-slate-700">{{ $s->nombre_plan }}</td>
                            <td class="py-2.5 text-slate-500">{{ $s->periodo_inicio->format('d/m/Y') }} → {{ $s->periodo_fin->format('d/m/Y') }}</td>
                            <td class="py-2.5 capitalize text-slate-600">{{ $s->metodo_pago }}</td>
                            <td class="py-2.5 font-mono text-xs text-slate-500">{{ $s->referencia }}</td>
                            <td class="py-2.5 text-right font-bold text-slate-700">S/ {{ number_format($s->monto, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-slate-400">Aún no tienes pagos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

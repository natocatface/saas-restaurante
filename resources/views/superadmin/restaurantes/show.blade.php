<x-superadmin-layout :title="$restaurante->nombre">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('superadmin.restaurantes.index') }}" class="text-sm font-semibold text-brand-600">← Restaurantes</a>
            <h1 class="text-2xl font-extrabold text-slate-800">{{ $restaurante->nombre }}</h1>
        </div>
        <span class="badge bg-{{ $restaurante->estadoColor() }}-50 text-{{ $restaurante->estadoColor() }}-600">{{ $restaurante->estadoLabel() }}</span>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <x-val-errors />
            <p class="mb-4 text-sm font-bold text-slate-700">Datos y suscripción</p>
            <form method="POST" action="{{ route('superadmin.restaurantes.update', $restaurante) }}" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre</label>
                        <input name="nombre" value="{{ old('nombre', $restaurante->nombre) }}" class="form-input-c" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email', $restaurante->email) }}" class="form-input-c">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Teléfono</label>
                        <input name="telefono" value="{{ old('telefono', $restaurante->telefono) }}" class="form-input-c">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Plan</label>
                        <select name="plan_id" class="form-input-c">
                            <option value="">— Sin plan —</option>
                            @foreach ($planes as $p)<option value="{{ $p->id }}" @selected(old('plan_id', $restaurante->plan_id)==$p->id)>{{ $p->nombre }} (S/ {{ number_format($p->precio,0) }})</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Estado</label>
                        <select name="estado" class="form-input-c">
                            @foreach (['trial'=>'Prueba','activo'=>'Activo','suspendido'=>'Suspendido','cancelado'=>'Cancelado'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('estado', $restaurante->estado)==$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="activo" value="1" @checked(old('activo', $restaurante->activo)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                            Cuenta activa
                        </label>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Fin de prueba</label>
                        <input type="date" name="trial_ends_at" value="{{ old('trial_ends_at', $restaurante->trial_ends_at?->toDateString()) }}" class="form-input-c">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Fin de suscripción</label>
                        <input type="date" name="subscription_ends_at" value="{{ old('subscription_ends_at', $restaurante->subscription_ends_at?->toDateString()) }}" class="form-input-c">
                    </div>
                </div>
                <div class="flex justify-end"><button class="btn-primary">Guardar cambios</button></div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="card">
                <p class="mb-3 text-sm font-bold text-slate-700">Información</p>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-400">Usuarios</dt><dd class="text-slate-700">{{ $restaurante->usuarios_count }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Slug</dt><dd class="font-mono text-xs text-slate-600">{{ $restaurante->slug }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Alta</dt><dd class="text-slate-700">{{ $restaurante->created_at->format('d/m/Y') }}</dd></div>
                </dl>
            </div>

            {{-- Acciones rápidas --}}
            <div class="card" x-data="{ tab: 'pago' }">
                <p class="mb-3 text-sm font-bold text-slate-700">Acciones rápidas</p>

                <div class="mb-3 grid grid-cols-2 gap-2">
                    <button @click="tab='pago'" :class="tab==='pago' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-lg py-1.5 text-xs font-semibold">Registrar pago</button>
                    <button @click="tab='prueba'" :class="tab==='prueba' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-lg py-1.5 text-xs font-semibold">Extender prueba</button>
                </div>

                {{-- Registrar pago --}}
                <form x-show="tab==='pago'" method="POST" action="{{ route('superadmin.restaurantes.pago', $restaurante) }}" class="space-y-2.5">
                    @csrf
                    <select name="plan_id" class="form-input-c text-sm">
                        @foreach ($planes as $p)<option value="{{ $p->id }}" @selected($restaurante->plan_id==$p->id)>{{ $p->nombre }} (S/ {{ number_format($p->precio,0) }})</option>@endforeach
                    </select>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" min="0" name="monto" value="{{ $restaurante->plan?->precio ?? 0 }}" class="form-input-c text-sm" placeholder="Monto" required>
                        <select name="intervalo" class="form-input-c text-sm"><option value="mensual">Mensual</option><option value="anual">Anual</option></select>
                    </div>
                    <select name="metodo_pago" class="form-input-c text-sm"><option value="transferencia">Transferencia</option><option value="tarjeta">Tarjeta</option><option value="efectivo">Efectivo</option><option value="yape">Yape</option><option value="plin">Plin</option></select>
                    <button class="btn-primary w-full text-sm">Registrar pago y activar</button>
                </form>

                {{-- Extender prueba --}}
                <form x-show="tab==='prueba'" method="POST" action="{{ route('superadmin.restaurantes.extender', $restaurante) }}" class="space-y-2.5" style="display:none">
                    @csrf
                    <label class="block text-xs text-slate-500">Días a sumar a la prueba</label>
                    <input type="number" min="1" max="365" name="dias" value="14" class="form-input-c text-sm" required>
                    <button class="btn-secondary w-full text-sm">Extender prueba</button>
                </form>

                {{-- Suspender / Activar --}}
                <form method="POST" action="{{ route('superadmin.restaurantes.toggle', $restaurante) }}" class="mt-3 border-t border-slate-100 pt-3">
                    @csrf @method('PATCH')
                    @if (in_array($restaurante->estado, ['suspendido','cancelado']))
                        <button class="w-full rounded-xl bg-emerald-50 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100">✓ Reactivar restaurante</button>
                    @else
                        <button onclick="return confirm('¿Suspender este restaurante?')" class="w-full rounded-xl bg-amber-50 py-2 text-sm font-semibold text-amber-700 ring-1 ring-amber-100 hover:bg-amber-100">⏸ Suspender restaurante</button>
                    @endif
                </form>
            </div>

            <div class="card">
                <p class="mb-3 text-sm font-bold text-slate-700">Historial de pagos</p>
                <div class="space-y-2 text-sm">
                    @forelse ($restaurante->suscripciones->sortByDesc('periodo_inicio') as $s)
                        <div class="flex items-center justify-between border-b border-slate-50 pb-2">
                            <div>
                                <p class="font-semibold text-slate-700">{{ $s->nombre_plan }}</p>
                                <p class="text-xs text-slate-400">{{ $s->periodo_inicio->format('d/m/Y') }} → {{ $s->periodo_fin->format('d/m/Y') }}</p>
                            </div>
                            <span class="font-bold text-slate-700">S/ {{ number_format($s->monto,2) }}</span>
                        </div>
                    @empty
                        <p class="text-slate-400">Sin pagos registrados.</p>
                    @endforelse
                </div>
            </div>

            <form method="POST" action="{{ route('superadmin.restaurantes.destroy', $restaurante) }}" onsubmit="return confirm('Esto eliminará el restaurante y TODOS sus datos. ¿Continuar?')" class="card">
                @csrf @method('DELETE')
                <p class="mb-2 text-sm font-bold text-rose-600">Zona peligrosa</p>
                <p class="mb-3 text-xs text-slate-400">Elimina permanentemente el restaurante y todos sus datos.</p>
                <button class="btn-danger w-full">Eliminar restaurante</button>
            </form>
        </div>
    </div>
</x-superadmin-layout>

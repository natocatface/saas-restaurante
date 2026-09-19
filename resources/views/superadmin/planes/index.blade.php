<x-superadmin-layout title="Planes">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-extrabold text-slate-800">Planes</h1>
        <a href="{{ route('superadmin.planes.create') }}" class="btn-primary">+ Nuevo plan</a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($planes as $plan)
            <div class="card flex flex-col {{ $plan->destacado ? 'ring-2 ring-brand-500' : '' }}">
                <div class="flex items-start justify-between">
                    <h3 class="text-lg font-extrabold text-slate-800">{{ $plan->nombre }}</h3>
                    <span class="badge {{ $plan->activo ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">{{ $plan->activo ? 'Activo' : 'Inactivo' }}</span>
                </div>
                <p class="text-sm text-slate-500">{{ $plan->descripcion }}</p>
                <p class="mt-3 text-3xl font-extrabold text-brand-600">S/ {{ number_format($plan->precio, 0) }}<span class="text-sm font-medium text-slate-400">/{{ $plan->intervalo === 'anual' ? 'año' : 'mes' }}</span></p>
                <ul class="mt-4 space-y-1.5 text-sm text-slate-600">
                    <li>🪑 {{ $plan->limiteTexto('mesas') }} mesas</li>
                    <li>🍽️ {{ $plan->limiteTexto('productos') }} productos</li>
                    <li>👤 {{ $plan->limiteTexto('usuarios') }} usuarios</li>
                </ul>
                <p class="mt-3 text-xs text-slate-400">{{ $plan->restaurantes_count }} restaurante(s) en este plan</p>
                <div class="mt-auto flex gap-2 pt-4">
                    <a href="{{ route('superadmin.planes.edit', $plan) }}" class="btn-secondary flex-1 py-1.5 text-xs">Editar</a>
                    <form method="POST" action="{{ route('superadmin.planes.destroy', $plan) }}" onsubmit="return confirm('¿Eliminar plan?')">
                        @csrf @method('DELETE')<button class="btn-danger py-1.5 text-xs">×</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="col-span-full rounded-2xl bg-white p-8 text-center text-slate-400">No hay planes. Crea el primero.</p>
        @endforelse
    </div>
</x-superadmin-layout>

<x-superadmin-layout title="Restaurantes">
    <h1 class="mb-6 text-2xl font-extrabold text-slate-800">Restaurantes</h1>

    <form method="GET" class="card mb-4 flex flex-wrap gap-3">
        <input name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o correo..." class="form-input-c max-w-xs">
        <select name="estado" class="form-input-c max-w-xs">
            <option value="">Todos los estados</option>
            @foreach (['trial'=>'Prueba','activo'=>'Activo','suspendido'=>'Suspendido','cancelado'=>'Cancelado'] as $k=>$v)
                <option value="{{ $k }}" @selected(request('estado')==$k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="btn-secondary">Filtrar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-100 text-left text-xs uppercase text-slate-400">
                <th class="py-2 font-semibold">Restaurante</th><th class="py-2 font-semibold">Plan</th>
                <th class="py-2 font-semibold">Usuarios</th><th class="py-2 font-semibold">Estado</th>
                <th class="py-2 font-semibold">Vence</th><th class="py-2 text-right font-semibold">Acciones</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($restaurantes as $r)
                    <tr>
                        <td class="py-3">
                            <p class="font-semibold text-slate-700">{{ $r->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $r->email ?: '—' }}</p>
                        </td>
                        <td class="py-3 text-slate-600">{{ $r->plan?->nombre ?? '—' }}</td>
                        <td class="py-3 text-slate-600">{{ $r->usuarios_count }}</td>
                        <td class="py-3"><span class="badge bg-{{ $r->estadoColor() }}-50 text-{{ $r->estadoColor() }}-600">{{ $r->estadoLabel() }}</span></td>
                        <td class="py-3 text-slate-500">
                            @php $fin = $r->estado === 'trial' ? $r->trial_ends_at : $r->subscription_ends_at; @endphp
                            {{ $fin ? $fin->format('d/m/Y') : '—' }}
                        </td>
                        <td class="py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('superadmin.restaurantes.show', $r) }}" class="btn-secondary py-1 text-xs">Gestionar</a>
                                <form method="POST" action="{{ route('superadmin.restaurantes.toggle', $r) }}">
                                    @csrf @method('PATCH')
                                    <button class="py-1 text-xs {{ in_array($r->estado,['suspendido','cancelado']) ? 'btn-primary' : 'btn-danger' }}">
                                        {{ in_array($r->estado,['suspendido','cancelado']) ? 'Activar' : 'Suspender' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-slate-400">No hay restaurantes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $restaurantes->links() }}</div>
</x-superadmin-layout>

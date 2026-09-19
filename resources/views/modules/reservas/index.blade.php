<x-app-layout title="Reservas">
    <x-page-header title="Reservas" subtitle="Gestiona las reservas de mesas.">
        <a href="{{ route('reservas.create') }}" class="btn-primary">+ Nueva reserva</a>
    </x-page-header>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('reservas.index') }}" class="badge {{ !request('estado') ? 'bg-brand-600 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200' }} px-4 py-1.5">Todas</a>
        @foreach (['pendiente'=>'Pendientes','confirmada'=>'Confirmadas','cumplida'=>'Cumplidas','cancelada'=>'Canceladas'] as $k=>$v)
            <a href="{{ route('reservas.index', ['estado'=>$k]) }}" class="badge {{ request('estado')==$k ? 'bg-brand-600 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200' }} px-4 py-1.5">{{ $v }}</a>
        @endforeach
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                    <th class="py-2 font-semibold">Cliente</th>
                    <th class="py-2 font-semibold">Fecha</th>
                    <th class="py-2 font-semibold">Hora</th>
                    <th class="py-2 font-semibold">Personas</th>
                    <th class="py-2 font-semibold">Mesa</th>
                    <th class="py-2 font-semibold">Estado</th>
                    <th class="py-2 text-right font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($reservas as $r)
                    @php $colors = ['pendiente'=>'amber','confirmada'=>'emerald','cumplida'=>'indigo','cancelada'=>'rose']; $c = $colors[$r->estado] ?? 'slate'; @endphp
                    <tr>
                        <td class="py-3">
                            <p class="font-semibold text-slate-700">{{ $r->nombre_cliente }}</p>
                            <p class="text-xs text-slate-400">{{ $r->telefono ?: '—' }}</p>
                        </td>
                        <td class="py-3 text-slate-600">{{ $r->fecha->isoFormat('DD MMM YYYY') }}</td>
                        <td class="py-3 text-slate-600">{{ \Illuminate\Support\Str::of($r->hora)->substr(0,5) }}</td>
                        <td class="py-3 text-slate-600">{{ $r->personas }}</td>
                        <td class="py-3 text-slate-600">{{ $r->mesa?->nombre ?? '—' }}</td>
                        <td class="py-3"><span class="badge bg-{{ $c }}-50 text-{{ $c }}-600">{{ ucfirst($r->estado) }}</span></td>
                        <td class="py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('reservas.edit', $r) }}" class="btn-secondary py-1 text-xs">Editar</a>
                                <form method="POST" action="{{ route('reservas.destroy', $r) }}" onsubmit="return confirm('¿Eliminar reserva?')">
                                    @csrf @method('DELETE')<button class="btn-danger py-1 text-xs">×</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-slate-400">No hay reservas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $reservas->links() }}</div>
</x-app-layout>

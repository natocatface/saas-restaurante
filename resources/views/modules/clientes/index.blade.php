<x-app-layout title="Clientes">
    <x-page-header title="Clientes" subtitle="Base de datos de tus comensales.">
        <a href="{{ route('clientes.create') }}" class="btn-primary">+ Nuevo cliente</a>
    </x-page-header>

    <form method="GET" class="card mb-4 flex gap-3">
        <input name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o documento..." class="form-input-c max-w-md">
        <button class="btn-secondary">Buscar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                    <th class="py-2 font-semibold">Cliente</th>
                    <th class="py-2 font-semibold">Documento</th>
                    <th class="py-2 font-semibold">Teléfono</th>
                    <th class="py-2 font-semibold">Pedidos</th>
                    <th class="py-2 font-semibold">Puntos</th>
                    <th class="py-2 text-right font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($clientes as $cl)
                    <tr>
                        <td class="py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-600">{{ mb_strtoupper(mb_substr($cl->nombre,0,2)) }}</span>
                                <div>
                                    <p class="font-semibold text-slate-700">{{ $cl->nombre }}</p>
                                    <p class="text-xs text-slate-400">{{ $cl->email ?: '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 text-slate-600">{{ $cl->documento ?: '—' }}</td>
                        <td class="py-3 text-slate-600">{{ $cl->telefono ?: '—' }}</td>
                        <td class="py-3 text-slate-600">{{ $cl->pedidos_count }}</td>
                        <td class="py-3"><span class="badge bg-amber-50 text-amber-600">{{ $cl->puntos }} pts</span></td>
                        <td class="py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('clientes.edit', $cl) }}" class="btn-secondary py-1 text-xs">Editar</a>
                                <form method="POST" action="{{ route('clientes.destroy', $cl) }}" onsubmit="return confirm('¿Eliminar cliente?')">
                                    @csrf @method('DELETE')<button class="btn-danger py-1 text-xs">×</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-slate-400">No hay clientes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $clientes->links() }}</div>
</x-app-layout>

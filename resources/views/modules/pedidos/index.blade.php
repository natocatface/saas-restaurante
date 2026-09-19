<x-app-layout title="Pedidos">
    @php $m = \App\Models\Restaurante::actual()->moneda; @endphp
    <x-page-header title="Pedidos" subtitle="Historial y seguimiento de pedidos.">
        <a href="{{ route('pos.index') }}" class="btn-primary">+ Nueva venta</a>
    </x-page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach (['pendiente'=>['Pendientes','amber'],'preparando'=>['Preparando','sky'],'servido'=>['Servidos','indigo'],'pagado_hoy'=>['Pagados hoy','emerald']] as $k=>$v)
            <div class="card"><p class="text-xl font-extrabold text-{{ $v[1] }}-600">{{ $resumen[$k] }}</p><p class="text-xs text-slate-400">{{ $v[0] }}</p></div>
        @endforeach
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('pedidos.index') }}" class="badge {{ !request('estado') ? 'bg-brand-600 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200' }} px-4 py-1.5">Todos</a>
        @foreach (['pendiente'=>'Pendiente','preparando'=>'Preparando','servido'=>'Servido','pagado'=>'Pagado','cancelado'=>'Cancelado'] as $k=>$v)
            <a href="{{ route('pedidos.index', ['estado'=>$k]) }}" class="badge {{ request('estado')==$k ? 'bg-brand-600 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200' }} px-4 py-1.5">{{ $v }}</a>
        @endforeach
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                    <th class="py-2 font-semibold">Código</th>
                    <th class="py-2 font-semibold">Tipo</th>
                    <th class="py-2 font-semibold">Mesa</th>
                    <th class="py-2 font-semibold">Atendido</th>
                    <th class="py-2 font-semibold">Fecha</th>
                    <th class="py-2 font-semibold">Estado</th>
                    <th class="py-2 text-right font-semibold">Total</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($pedidos as $p)
                    <tr>
                        <td class="py-3 font-mono text-xs font-semibold text-slate-600">{{ $p->codigo }}</td>
                        <td class="py-3 text-slate-600 capitalize">{{ $p->tipo }}</td>
                        <td class="py-3 text-slate-600">{{ $p->mesa?->nombre ?? '—' }}</td>
                        <td class="py-3 text-slate-600">{{ $p->user?->name ?? '—' }}</td>
                        <td class="py-3 text-slate-600">{{ $p->created_at->format('d/m H:i') }}</td>
                        <td class="py-3"><span class="badge bg-{{ $p->color_estado }}-50 text-{{ $p->color_estado }}-600">{{ ucfirst($p->estado) }}</span></td>
                        <td class="py-3 text-right font-bold text-slate-700">{{ $m }} {{ number_format($p->total,2) }}</td>
                        <td class="py-3 text-right"><a href="{{ route('pedidos.show', $p) }}" class="btn-secondary py-1 text-xs">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-6 text-center text-slate-400">No hay pedidos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $pedidos->links() }}</div>
</x-app-layout>

<x-app-layout title="Promociones">
    @php $m = \App\Models\Restaurante::actual()->moneda; @endphp
    <x-page-header title="Promociones y descuentos" subtitle="Crea cupones y ofertas aplicables en el Punto de Venta.">
        <a href="{{ route('promociones.create') }}" class="btn-primary">+ Nueva promoción</a>
    </x-page-header>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                <th class="py-2 font-semibold">Nombre</th><th class="py-2 font-semibold">Cupón</th><th class="py-2 font-semibold">Descuento</th>
                <th class="py-2 font-semibold">Alcance</th><th class="py-2 font-semibold">Vigencia</th><th class="py-2 font-semibold">Estado</th>
                <th class="py-2 text-right font-semibold">Acciones</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($promociones as $promo)
                    <tr>
                        <td class="py-3 font-semibold text-slate-700">{{ $promo->nombre }}</td>
                        <td class="py-3">@if($promo->codigo)<span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">{{ $promo->codigo }}</span>@else<span class="text-slate-300">—</span>@endif</td>
                        <td class="py-3 font-semibold text-brand-600">{{ $promo->tipo==='porcentaje' ? $promo->valor.'%' : $m.' '.number_format($promo->valor,2) }}</td>
                        <td class="py-3 text-slate-600">{{ $promo->alcance==='producto' ? ($promo->producto?->nombre ?? 'Producto') : 'Todo el pedido' }}</td>
                        <td class="py-3 text-xs text-slate-500">{{ $promo->inicia_at?->format('d/m/y') ?? '—' }} → {{ $promo->termina_at?->format('d/m/y') ?? '∞' }}</td>
                        <td class="py-3">@if($promo->vigente())<span class="badge bg-emerald-50 text-emerald-600">Vigente</span>@else<span class="badge bg-slate-100 text-slate-500">Inactiva</span>@endif</td>
                        <td class="py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('promociones.edit', $promo) }}" class="btn-secondary py-1 text-xs">Editar</a>
                                <form method="POST" action="{{ route('promociones.destroy', $promo) }}" onsubmit="return confirm('¿Eliminar promoción?')">@csrf @method('DELETE')<button class="btn-danger py-1 text-xs">×</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-slate-400">No hay promociones. Crea la primera.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $promociones->links() }}</div>
</x-app-layout>

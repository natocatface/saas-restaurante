<x-app-layout title="Facturación electrónica">
    <x-page-header title="Facturación electrónica"
        subtitle="Comprobantes emitidos ante la autoridad fiscal (SUNAT / DIAN / SII / ARCA / SAT).">
        <form method="POST" action="{{ route('facturacion.resumen') }}"
              onsubmit="return confirm('¿Enviar a SUNAT el resumen diario de boletas de hoy?')">
            @csrf
            <button type="submit" class="btn btn-outline text-sm">Enviar resumen diario</button>
        </form>
    </x-page-header>

    @include('modules.facturacion._banner')

    @if(session('status'))
        <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif

    <div class="card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-left text-slate-500">
                    <th class="py-2 pr-4">Comprobante</th>
                    <th class="py-2 pr-4">Tipo</th>
                    <th class="py-2 pr-4">País</th>
                    <th class="py-2 pr-4">Total</th>
                    <th class="py-2 pr-4">Estado</th>
                    <th class="py-2 pr-4">Emitido</th>
                    <th class="py-2 pr-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($comprobantes as $c)
                    <tr class="border-b last:border-0">
                        <td class="py-2 pr-4 font-semibold text-slate-700">{{ $c->serie }}-{{ str_pad($c->correlativo, 8, '0', STR_PAD_LEFT) }}</td>
                        <td class="py-2 pr-4 capitalize">{{ str_replace('_', ' ', $c->tipo) }}</td>
                        <td class="py-2 pr-4">{{ $c->pais }}</td>
                        <td class="py-2 pr-4">{{ $c->moneda }} {{ number_format((float)$c->total, 2) }}</td>
                        <td class="py-2 pr-4">
                            <span class="badge bg-slate-100 text-slate-600">{{ ucfirst($c->estado->value) }}</span>
                        </td>
                        <td class="py-2 pr-4 text-slate-500">{{ $c->enviado_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="py-2 pr-4 text-right">
                            <a href="{{ route('facturacion.imprimir', $c) }}" target="_blank"
                               class="text-xs font-semibold text-brand-600 hover:underline">Imprimir</a>
                            @if($c->xml_path)
                                <span class="ml-2 text-xs text-slate-400">XML ✔</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-400">Aún no hay comprobantes emitidos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $comprobantes->links() }}</div>
</x-app-layout>

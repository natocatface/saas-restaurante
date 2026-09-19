@php $feRest = \App\Models\Restaurante::actual(); @endphp
<div class="mb-5 flex items-center gap-4 rounded-2xl border border-red-100 bg-gradient-to-r from-red-50 via-white to-red-50 px-5 py-4">
    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-red-100">
        {{-- Bandera del Perú --}}
        <svg viewBox="0 0 9 6" class="h-7 w-10 overflow-hidden rounded-sm ring-1 ring-slate-200">
            <rect width="3" height="6" x="0" fill="#D91023"/>
            <rect width="3" height="6" x="3" fill="#ffffff"/>
            <rect width="3" height="6" x="6" fill="#D91023"/>
        </svg>
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-extrabold text-slate-800">Facturación Electrónica — Perú</p>
        <p class="text-xs text-slate-500">
            Emisión de comprobantes conforme a <span class="font-semibold text-slate-600">SUNAT</span>:
            facturas, boletas, notas de crédito, comunicación de baja y resumen diario.
        </p>
    </div>
    <div class="hidden flex-col items-end gap-1.5 sm:flex">
        <span class="badge {{ $feRest?->fe_activo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
            {{ $feRest?->fe_activo ? '● Activa' : 'Inactiva' }}
        </span>
        <span class="rounded-full px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide {{ ($feRest?->sunat_ambiente ?? 'beta') === 'produccion' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
            {{ ($feRest?->sunat_ambiente ?? 'beta') === 'produccion' ? 'Producción' : 'Pruebas (beta)' }}
        </span>
    </div>
</div>

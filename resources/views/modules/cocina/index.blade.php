<x-app-layout title="Cocina (KDS)">
    <div x-data="kds()" x-init="init()" class="space-y-6">

        {{-- Encabezado --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Comandas a Cocina</h1>
                <p class="mt-0.5 text-sm text-slate-500">Tablero en tiempo real · se actualiza solo cada 7 segundos.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    </span>
                    En vivo
                </span>
                <button @click="load()" class="btn-secondary">
                    <svg class="h-4 w-4" :class="loading && 'animate-spin'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Actualizar
                </button>
            </div>
        </div>

        {{-- Columnas --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

            {{-- PENDIENTES --}}
            <section class="rounded-2xl bg-amber-50/60 p-3 ring-1 ring-amber-100">
                <div class="mb-3 flex items-center justify-between px-2">
                    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-amber-700">⏳ Pendientes</h2>
                    <span class="rounded-full bg-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-800" x-text="data.pendientes.length"></span>
                </div>
                <div class="space-y-3">
                    <template x-for="p in data.pendientes" :key="p.id">
                        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                            <div x-html="cardHead(p)"></div>
                            <div x-html="cardItems(p)"></div>
                            <button @click="avanzar(p)" class="mt-3 w-full rounded-lg bg-brand-600 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">▶ Empezar a preparar</button>
                        </div>
                    </template>
                    <p x-show="!data.pendientes.length" class="px-2 py-8 text-center text-sm text-amber-700/60">Sin comandas pendientes 🎉</p>
                </div>
            </section>

            {{-- PREPARANDO --}}
            <section class="rounded-2xl bg-orange-50/60 p-3 ring-1 ring-orange-100">
                <div class="mb-3 flex items-center justify-between px-2">
                    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-brand-700">🔥 En preparación</h2>
                    <span class="rounded-full bg-orange-200 px-2.5 py-0.5 text-xs font-bold text-brand-800" x-text="data.preparando.length"></span>
                </div>
                <div class="space-y-3">
                    <template x-for="p in data.preparando" :key="p.id">
                        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                            <div x-html="cardHead(p)"></div>
                            <div x-html="cardItems(p)"></div>
                            <button @click="avanzar(p)" class="mt-3 w-full rounded-lg bg-emerald-600 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">✓ Marcar como listo</button>
                        </div>
                    </template>
                    <p x-show="!data.preparando.length" class="px-2 py-8 text-center text-sm text-brand-700/50">Nada en preparación.</p>
                </div>
            </section>

            {{-- LISTOS --}}
            <section class="rounded-2xl bg-emerald-50/60 p-3 ring-1 ring-emerald-100">
                <div class="mb-3 flex items-center justify-between px-2">
                    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-emerald-700">✅ Listos (recientes)</h2>
                    <span class="rounded-full bg-emerald-200 px-2.5 py-0.5 text-xs font-bold text-emerald-800" x-text="data.listos.length"></span>
                </div>
                <div class="space-y-3">
                    <template x-for="p in data.listos" :key="p.id">
                        <div class="rounded-xl bg-white p-4 opacity-80 shadow-sm ring-1 ring-slate-100">
                            <div x-html="cardHead(p)"></div>
                            <div x-html="cardItems(p)"></div>
                        </div>
                    </template>
                    <p x-show="!data.listos.length" class="px-2 py-8 text-center text-sm text-emerald-700/50">Aún no hay platos listos.</p>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
    <script>
        function kds() {
            return {
                loading: false,
                timer: null,
                data: { pendientes: [], preparando: [], listos: [] },
                csrf: document.querySelector('meta[name="csrf-token"]').content,
                init() {
                    this.load();
                    this.timer = setInterval(() => this.load(), 7000);
                    window.addEventListener('beforeunload', () => clearInterval(this.timer));
                },
                async load() {
                    this.loading = true;
                    try {
                        const r = await fetch("{{ route('cocina.data') }}", { headers: { 'Accept': 'application/json' } });
                        this.data = await r.json();
                    } catch (e) { /* silencio: reintenta en el próximo ciclo */ }
                    this.loading = false;
                },
                async avanzar(p) {
                    await fetch(`/cocina/${p.id}/avanzar`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                    });
                    this.load();
                },
                urgencia(min) {
                    if (min >= 15) return 'bg-rose-100 text-rose-700';
                    if (min >= 8)  return 'bg-amber-100 text-amber-700';
                    return 'bg-emerald-100 text-emerald-700';
                },
                cardHead(p) {
                    const destino = p.mesa ? `Mesa ${p.mesa}` : (p.tipo === 'delivery' ? '🛵 Delivery' : '🥡 Para llevar');
                    return `
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-mono text-slate-400">${p.codigo}</p>
                                <p class="font-extrabold text-slate-800">${destino}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold ${this.urgencia(p.minutos)}">${p.minutos} min</span>
                        </div>`;
                },
                cardItems(p) {
                    const filas = p.items.map(i => `
                        <li class="flex items-start gap-2 py-1 text-sm">
                            <span class="mt-0.5 flex h-5 min-w-[20px] items-center justify-center rounded bg-brand-600 px-1 text-xs font-bold text-white">${i.cantidad}</span>
                            <span class="flex-1 text-slate-700">${i.nombre}${i.notas ? `<span class="block text-xs text-slate-400">📝 ${i.notas}</span>` : ''}</span>
                        </li>`).join('');
                    const nota = p.notas ? `<p class="mt-2 rounded-lg bg-amber-50 px-2 py-1 text-xs text-amber-700">📝 ${p.notas}</p>` : '';
                    return `<ul class="mt-3 divide-y divide-slate-50">${filas}</ul>${nota}`;
                },
            }
        }
    </script>
    @endpush
</x-app-layout>

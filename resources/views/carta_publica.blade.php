<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carta · {{ $restaurante->nombre }}</title>
    <meta name="description" content="Carta digital de {{ $restaurante->nombre }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#fdf8f2] text-slate-700" x-data="carta()">
    @php $moneda = $restaurante->moneda ?: 'S/'; @endphp

    {{-- Confirmación de pedido --}}
    @if(session('pedido_ok'))
        <div class="fixed inset-x-0 top-0 z-50 bg-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white shadow-lg">
            ✅ ¡Pedido {{ session('pedido_ok') }} enviado! El restaurante lo está preparando.
        </div>
    @endif

    {{-- Cabecera --}}
    <header class="relative overflow-hidden bg-gradient-to-br from-brand-600 to-brand-800 px-6 pb-10 pt-12 text-center text-white">
        <div class="absolute -top-16 right-0 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
        <div class="relative">
            @if($restaurante->logo)
                <img src="{{ \Illuminate\Support\Str::startsWith($restaurante->logo,'http') ? $restaurante->logo : asset('storage/'.$restaurante->logo) }}" alt="" class="mx-auto mb-3 h-20 w-20 rounded-2xl object-cover ring-2 ring-white/40">
            @else
                <div class="mx-auto mb-3 flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 text-4xl backdrop-blur">🍽️</div>
            @endif
            <h1 class="text-2xl font-extrabold">{{ $restaurante->nombre }}</h1>
            <p class="mt-1 text-sm text-orange-100/80">Carta digital</p>
            @if($mesa)<p class="mt-2 inline-block rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">📍 Mesa {{ $mesa }}</p>@endif
        </div>
    </header>

    {{-- Índice de categorías --}}
    @if($categorias->count() > 1)
        <nav class="sticky top-0 z-10 -mt-4 overflow-x-auto border-b border-orange-100 bg-[#fdf8f2]/95 px-4 py-3 backdrop-blur">
            <div class="flex gap-2">
                @foreach($categorias as $cat)
                    <a href="#cat-{{ $cat->id }}" class="whitespace-nowrap rounded-full bg-white px-3.5 py-1.5 text-sm font-semibold text-brand-700 shadow-sm ring-1 ring-orange-100">{{ $cat->nombre }}</a>
                @endforeach
            </div>
        </nav>
    @endif

    {{-- Menú --}}
    <main class="mx-auto max-w-2xl px-4 py-6 pb-28">
        @forelse($categorias as $cat)
            <section id="cat-{{ $cat->id }}" class="mb-8 scroll-mt-20">
                <h2 class="mb-1 text-xl font-extrabold text-slate-900">{{ $cat->nombre }}</h2>
                @if($cat->descripcion)<p class="mb-4 text-sm text-slate-500">{{ $cat->descripcion }}</p>@else<div class="mb-4"></div>@endif

                <div class="space-y-3">
                    @foreach($cat->productos as $p)
                        <article class="flex items-center gap-4 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-orange-100/70">
                            @if($p->imagen)
                                <img src="{{ \Illuminate\Support\Str::startsWith($p->imagen,'http') ? $p->imagen : asset('storage/'.$p->imagen) }}" alt="{{ $p->nombre }}" class="h-20 w-20 shrink-0 rounded-xl object-cover">
                            @else
                                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-100 to-orange-200 text-3xl">🍴</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-slate-800">{{ $p->nombre }}</h3>
                                @if($p->descripcion)<p class="mt-0.5 line-clamp-2 text-sm text-slate-500">{{ $p->descripcion }}</p>@endif
                                <p class="mt-1 text-lg font-extrabold text-brand-600">{{ $moneda }} {{ number_format((float)$p->precio, 2) }}</p>
                            </div>
                            <button type="button" @click="add({{ $p->id }}, @js($p->nombre), {{ (float)$p->precio }})"
                                    class="shrink-0 rounded-xl bg-brand-600 px-3 py-2 text-sm font-bold text-white transition hover:bg-brand-700">＋</button>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="py-20 text-center">
                <p class="text-5xl">🍽️</p>
                <p class="mt-4 font-semibold text-slate-600">La carta aún no está disponible.</p>
            </div>
        @endforelse
    </main>

    {{-- Barra flotante del carrito --}}
    <div x-show="count > 0" x-cloak class="fixed inset-x-0 bottom-0 z-30 border-t border-orange-100 bg-white/95 p-3 backdrop-blur">
        <div class="mx-auto flex max-w-2xl items-center gap-3">
            <div class="flex-1">
                <p class="text-xs text-slate-400"><span x-text="count"></span> producto(s)</p>
                <p class="text-lg font-extrabold text-slate-800" x-text="money(total)"></p>
            </div>
            <button @click="abierto = true" class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white">Ver mi pedido →</button>
        </div>
    </div>

    {{-- Modal del pedido --}}
    <div x-show="abierto" x-cloak class="fixed inset-0 z-40 flex items-end justify-center bg-black/50 sm:items-center" @click.self="abierto=false">
        <div class="max-h-[90vh] w-full max-w-md overflow-auto rounded-t-3xl bg-white p-5 sm:rounded-3xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-extrabold text-slate-900">Tu pedido</h2>
                <button @click="abierto=false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">✕</button>
            </div>

            <div class="space-y-2">
                <template x-for="(it, idx) in items" :key="it.id">
                    <div class="flex items-center gap-2 rounded-xl bg-slate-50 p-2">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-700" x-text="it.nombre"></p>
                            <p class="text-xs text-slate-400" x-text="money(it.precio)"></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="dec(idx)" class="h-7 w-7 rounded-lg bg-white text-slate-600 ring-1 ring-slate-200">−</button>
                            <span class="w-6 text-center text-sm font-bold" x-text="it.cantidad"></span>
                            <button type="button" @click="inc(idx)" class="h-7 w-7 rounded-lg bg-white text-slate-600 ring-1 ring-slate-200">＋</button>
                        </div>
                        <p class="w-16 text-right text-sm font-bold text-slate-700" x-text="money(it.precio*it.cantidad)"></p>
                    </div>
                </template>
            </div>

            <form method="POST" action="{{ route('carta.pedido', $restaurante->slug) }}" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="tipo" :value="tipo">
                <template x-for="(it, idx) in items" :key="'h'+it.id">
                    <span>
                        <input type="hidden" :name="'items['+idx+'][id]'" :value="it.id">
                        <input type="hidden" :name="'items['+idx+'][cantidad]'" :value="it.cantidad">
                    </span>
                </template>

                <div class="flex gap-2">
                    <button type="button" @click="tipo='mesa'" :class="tipo==='mesa' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600'" class="flex-1 rounded-lg py-2 text-sm font-semibold">En mesa</button>
                    <button type="button" @click="tipo='llevar'" :class="tipo==='llevar' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600'" class="flex-1 rounded-lg py-2 text-sm font-semibold">Para llevar</button>
                </div>

                <input type="text" name="nombre" placeholder="Tu nombre" class="w-full rounded-xl border-slate-200 text-sm">
                <input type="text" name="mesa" x-show="tipo==='mesa'" value="{{ $mesa }}" placeholder="N° de mesa" class="w-full rounded-xl border-slate-200 text-sm">
                <textarea name="notas" rows="2" placeholder="Notas (ej. sin cebolla, término de cocción...)" class="w-full rounded-xl border-slate-200 text-sm"></textarea>

                <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                    <span class="text-sm text-slate-500">Total</span>
                    <span class="text-xl font-extrabold text-slate-900" x-text="money(total)"></span>
                </div>
                <p class="text-center text-[11px] text-slate-400">El total no incluye posibles ajustes; el pago se realiza en el local.</p>
                <button type="submit" :disabled="items.length===0" class="w-full rounded-xl bg-brand-600 py-3 text-sm font-bold text-white disabled:opacity-50">Enviar pedido a cocina</button>
            </form>
        </div>
    </div>

    <footer class="border-t border-orange-100 py-6 text-center">
        <p class="text-xs text-slate-400">{{ $restaurante->nombre }} · Carta digital</p>
        <p class="mt-1 text-[11px] text-slate-300">Powered by Mi Restaurante VIP</p>
    </footer>

    <script>
        function carta() {
            return {
                items: [], abierto: false, tipo: '{{ $mesa ? 'mesa' : 'llevar' }}',
                add(id, nombre, precio) {
                    const f = this.items.find(i => i.id === id);
                    if (f) f.cantidad++; else this.items.push({ id, nombre, precio, cantidad: 1 });
                },
                inc(i) { this.items[i].cantidad++; },
                dec(i) { if (--this.items[i].cantidad <= 0) this.items.splice(i, 1); if (this.items.length === 0) this.abierto = false; },
                get count() { return this.items.reduce((s, i) => s + i.cantidad, 0); },
                get total() { return this.items.reduce((s, i) => s + i.precio * i.cantidad, 0); },
                money(v) { return '{{ $moneda }} ' + Number(v).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
            }
        }
    </script>
</body>
</html>

<x-app-layout title="Punto de Venta">
    @php
        $m = $config->moneda;
        $prods = $productos->map(fn($p) => [
            'id' => $p->id, 'nombre' => $p->nombre, 'precio' => (float) $p->precio,
            'categoria_id' => $p->categoria_id, 'icono' => $p->categoria->icono ?? '🍽️',
        ])->values();
        $promosJson = $promos->map(fn($p) => [
            'id' => $p->id, 'codigo' => $p->codigo, 'tipo' => $p->tipo, 'valor' => (float) $p->valor,
            'alcance' => $p->alcance, 'producto_id' => $p->producto_id, 'min_compra' => (float) ($p->min_compra ?? 0),
            'vigente' => $p->vigente(),
        ])->values();
        $clientesPuntosJson = $clientes->mapWithKeys(fn($c) => [$c->id => (int) $c->puntos]);
    @endphp

    <div x-data="pos()" class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Productos --}}
        <div class="lg:col-span-2">
            <div class="card mb-4">
                <div class="flex flex-wrap items-center gap-2">
                    <input type="text" x-model="search" placeholder="Buscar producto..." class="form-input-c max-w-xs">
                    <button @click="cat=null" :class="cat===null ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-xl px-3 py-1.5 text-xs font-semibold">Todos</button>
                    @foreach ($categorias as $c)
                        <button @click="cat={{ $c->id }}" :class="cat==={{ $c->id }} ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-xl px-3 py-1.5 text-xs font-semibold">{{ $c->icono }} {{ $c->nombre }}</button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                <template x-for="p in filtered" :key="p.id">
                    <button @click="add(p)" class="card flex flex-col items-center text-center transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-50 to-accent-50 text-3xl" x-text="p.icono"></div>
                        <p class="text-sm font-bold text-slate-700" x-text="p.nombre"></p>
                        <p class="mt-1 text-sm font-extrabold text-brand-600" x-text="money(p.precio)"></p>
                    </button>
                </template>
            </div>
            <p x-show="filtered.length===0" class="mt-6 text-center text-slate-400">No hay productos que coincidan.</p>
        </div>

        {{-- Carrito --}}
        <div>
            <div class="card sticky top-20">
                <p class="mb-3 text-sm font-bold text-slate-700">🛒 Pedido actual</p>

                <form method="POST" action="{{ route('pos.store') }}">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <select name="tipo" x-model="tipo" class="form-input-c text-sm">
                            <option value="mesa">En mesa</option>
                            <option value="llevar">Para llevar</option>
                            <option value="delivery">Delivery</option>
                        </select>
                        <select name="mesa_id" x-show="tipo==='mesa'" class="form-input-c text-sm">
                            <option value="">Sin mesa</option>
                            @foreach ($mesas as $ms)<option value="{{ $ms->id }}">{{ $ms->nombre ?? 'Mesa '.$ms->numero }}</option>@endforeach
                        </select>
                    </div>

                    <select name="cliente_id" x-model="clienteId" class="form-input-c mt-2 text-sm">
                        <option value="">Cliente genérico</option>
                        @foreach ($clientes as $cl)<option value="{{ $cl->id }}">{{ $cl->nombre }}</option>@endforeach
                    </select>

                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <select name="promocion_id" x-model="promoId" class="form-input-c text-sm">
                            <option value="">Sin promoción</option>
                            @foreach ($promos as $pr)<option value="{{ $pr->id }}">{{ $pr->nombre }}</option>@endforeach
                        </select>
                        <input type="text" name="codigo_promo" x-model="codigoPromo" placeholder="Cupón" class="form-input-c text-sm">
                    </div>

                    <div x-show="clienteId && puntosDisponibles > 0" class="mt-2 flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2 text-xs" style="display:none">
                        <span class="text-amber-700">Puntos disponibles: <strong x-text="puntosDisponibles"></strong></span>
                        <input type="number" min="0" :max="puntosDisponibles" name="usar_puntos" x-model.number="usarPuntos" placeholder="Usar" class="ml-auto w-20 rounded border-amber-200 py-1 text-right text-xs">
                    </div>

                    <div class="my-3 max-h-72 space-y-2 overflow-y-auto">
                        <template x-for="(it, idx) in cart" :key="it.id">
                            <div class="flex items-center gap-2 rounded-xl bg-slate-50 p-2">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-700" x-text="it.nombre"></p>
                                    <p class="text-xs text-slate-400" x-text="money(it.precio)"></p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="dec(idx)" class="h-6 w-6 rounded-lg bg-white text-slate-600 ring-1 ring-slate-200">−</button>
                                    <span class="w-6 text-center text-sm font-bold" x-text="it.cantidad"></span>
                                    <button type="button" @click="inc(idx)" class="h-6 w-6 rounded-lg bg-white text-slate-600 ring-1 ring-slate-200">+</button>
                                </div>
                                <p class="w-16 text-right text-sm font-bold text-slate-700" x-text="money(it.precio*it.cantidad)"></p>
                            </div>
                        </template>
                        <p x-show="cart.length===0" class="py-6 text-center text-sm text-slate-400">Selecciona productos para agregar.</p>
                    </div>

                    <template x-for="(it, idx) in cart" :key="'h'+it.id">
                        <span>
                            <input type="hidden" :name="'items['+idx+'][id]'" :value="it.id">
                            <input type="hidden" :name="'items['+idx+'][cantidad]'" :value="it.cantidad">
                        </span>
                    </template>

                    <div class="space-y-1 border-t border-slate-100 pt-3 text-sm">
                        <div class="flex justify-between text-slate-500"><span>Subtotal</span><span x-text="money(subtotal)"></span></div>
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Descuento manual</span>
                            <input type="number" step="0.01" min="0" name="descuento" x-model.number="descuento" class="w-24 rounded-lg border-slate-200 py-1 text-right text-sm">
                        </div>
                        <div x-show="descPromo > 0" class="flex justify-between text-emerald-600" style="display:none"><span>Promoción</span><span x-text="'- ' + money(descPromo)"></span></div>
                        <div x-show="descPuntos > 0" class="flex justify-between text-emerald-600" style="display:none"><span>Puntos canjeados</span><span x-text="'- ' + money(descPuntos)"></span></div>
                        <div class="flex justify-between text-slate-500"><span>IGV ({{ $config->igv }}%)</span><span x-text="money(igv)"></span></div>
                        <div class="flex justify-between text-base font-extrabold text-slate-800"><span>Total</span><span x-text="money(total)"></span></div>
                    </div>

                    <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="pagar" value="1" x-model="pagar" class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                        Cobrar ahora
                    </label>
                    <select name="metodo_pago" x-show="pagar" class="form-input-c mt-2 text-sm">
                        <option value="efectivo">Efectivo</option><option value="tarjeta">Tarjeta</option>
                        <option value="yape">Yape</option><option value="plin">Plin</option><option value="transferencia">Transferencia</option>
                    </select>

                    <button type="submit" :disabled="cart.length===0" class="btn-primary mt-3 w-full disabled:cursor-not-allowed disabled:opacity-50">
                        <span x-text="pagar ? 'Cobrar y registrar' : 'Registrar pedido'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function pos() {
            return {
                products: @json($prods),
                cart: [], search: '', cat: null, tipo: 'mesa', descuento: 0, pagar: false,
                clienteId: '', promoId: '', codigoPromo: '', usarPuntos: 0,
                promos: @json($promosJson),
                clientesPuntos: @json($clientesPuntosJson),
                valorPunto: {{ \App\Models\Cliente::VALOR_PUNTO }},
                igvRate: {{ (float) $config->igv }} / 100,
                get puntosDisponibles() { return this.clientesPuntos[this.clienteId] || 0; },
                get lineTotals() { const m = {}; this.cart.forEach(i => { m[i.id] = (m[i.id] || 0) + i.precio * i.cantidad; }); return m; },
                get descPromo() {
                    let p = this.promos.find(x => x.id == this.promoId);
                    if (!p && this.codigoPromo) p = this.promos.find(x => x.codigo && x.codigo.toLowerCase() === this.codigoPromo.trim().toLowerCase());
                    if (!p || !p.vigente) return 0;
                    if (p.min_compra && this.subtotal < p.min_compra) return 0;
                    let base = p.alcance === 'producto' ? (this.lineTotals[p.producto_id] || 0) : this.subtotal;
                    if (base <= 0) return 0;
                    let d = p.tipo === 'porcentaje' ? base * p.valor / 100 : Math.min(p.valor, base);
                    return Math.min(d, this.subtotal);
                },
                get descPuntos() {
                    if (!this.clienteId) return 0;
                    const u = Math.min(this.usarPuntos || 0, this.puntosDisponibles);
                    return Math.round(u * this.valorPunto * 100) / 100;
                },
                get descuentoTotal() { return Math.min(this.subtotal, (this.descuento || 0) + this.descPromo + this.descPuntos); },
                get filtered() {
                    return this.products.filter(p =>
                        (this.cat === null || p.categoria_id === this.cat) &&
                        p.nombre.toLowerCase().includes(this.search.toLowerCase())
                    );
                },
                add(p) {
                    const f = this.cart.find(i => i.id === p.id);
                    if (f) f.cantidad++; else this.cart.push({ ...p, cantidad: 1 });
                },
                inc(i) { this.cart[i].cantidad++; },
                dec(i) { if (--this.cart[i].cantidad <= 0) this.cart.splice(i, 1); },
                get subtotal() { return this.cart.reduce((s, i) => s + i.precio * i.cantidad, 0); },
                get base() { return Math.max(0, this.subtotal - this.descuentoTotal); },
                get igv() { return this.base * this.igvRate; },
                get total() { return this.base + this.igv; },
                money(v) { return '{{ $m }} ' + Number(v).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
            };
        }
    </script>
    @endpush
</x-app-layout>

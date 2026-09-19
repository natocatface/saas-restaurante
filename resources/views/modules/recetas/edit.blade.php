<x-app-layout title="Receta">
    @php
        $moneda = \App\Models\Restaurante::actual()->moneda;
        $insumosJson = $insumos->map(fn($i) => ['id'=>$i->id,'nombre'=>$i->nombre,'unidad'=>$i->unidad,'costo'=>(float)$i->costo]);
        $recetaJson = $producto->recetas->map(fn($r) => ['insumo_id'=>$r->insumo_id,'cantidad'=>(float)$r->cantidad]);
    @endphp

    <x-page-header title="Receta · {{ $producto->nombre }}" subtitle="{{ $producto->categoria->nombre ?? '' }} · Precio de venta {{ $moneda }} {{ number_format($producto->precio,2) }}">
        <a href="{{ route('productos.index') }}" class="btn-secondary">← Volver</a>
    </x-page-header>

    @if($insumos->isEmpty())
        <div class="card text-center">
            <p class="text-4xl">📦</p>
            <p class="mt-3 font-semibold text-slate-700">Aún no tienes insumos registrados.</p>
            <p class="text-sm text-slate-400">Crea insumos en Inventario para poder armar recetas.</p>
            <a href="{{ route('insumos.index') }}" class="btn-primary mt-4">Ir a Inventario</a>
        </div>
    @else
    <div x-data="receta()" x-init="init()">
        <form method="POST" action="{{ route('recetas.update', $producto) }}">
            @csrf @method('PUT')

            {{-- Resumen en vivo --}}
            <div class="mb-6 grid grid-cols-3 gap-4">
                <div class="card"><p class="text-xs font-semibold uppercase text-slate-400">Precio venta</p><p class="mt-1 text-2xl font-extrabold text-slate-900">{{ $moneda }} {{ number_format($producto->precio,2) }}</p></div>
                <div class="card"><p class="text-xs font-semibold uppercase text-slate-400">Costo receta</p><p class="mt-1 text-2xl font-extrabold text-brand-600" x-text="'{{ $moneda }} ' + costoTotal.toFixed(2)"></p></div>
                <div class="card" :class="margen < 0 && 'ring-2 ring-accent-400'">
                    <p class="text-xs font-semibold uppercase text-slate-400">Margen</p>
                    <p class="mt-1 text-2xl font-extrabold" :class="margen >= 50 ? 'text-emerald-600' : (margen >= 20 ? 'text-amber-600' : 'text-accent-600')" x-text="precio > 0 ? margen.toFixed(1)+'%' : '—'"></p>
                    <p class="text-xs text-slate-400" x-text="'Ganancia: {{ $moneda }} ' + (precio - costoTotal).toFixed(2)"></p>
                </div>
            </div>

            {{-- Ingredientes --}}
            <div class="card">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Ingredientes / insumos</h3>
                    <button type="button" @click="agregar()" class="btn-secondary py-1.5 text-xs">+ Agregar insumo</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(fila, idx) in filas" :key="idx">
                        <div class="flex flex-wrap items-center gap-3 rounded-xl bg-slate-50 p-3">
                            <select :name="`items[${idx}][insumo_id]`" x-model.number="fila.insumo_id" @change="recalc()" class="form-input-c flex-1 min-w-[180px]">
                                <option value="">Selecciona un insumo…</option>
                                <template x-for="i in insumos" :key="i.id">
                                    <option :value="i.id" x-text="i.nombre + ' (' + i.unidad + ')'"></option>
                                </template>
                            </select>
                            <input type="number" step="0.001" min="0" :name="`items[${idx}][cantidad]`" x-model.number="fila.cantidad" @input="recalc()" class="form-input-c w-28" placeholder="Cantidad">
                            <span class="w-20 text-sm text-slate-500" x-text="unidadDe(fila.insumo_id)"></span>
                            <span class="w-28 text-right text-sm font-semibold text-slate-700" x-text="'{{ $moneda }} ' + subtotal(fila).toFixed(2)"></span>
                            <button type="button" @click="quitar(idx)" class="rounded-lg bg-accent-50 px-2.5 py-1.5 text-accent-600 hover:bg-accent-100">✕</button>
                        </div>
                    </template>
                    <p x-show="!filas.length" class="py-8 text-center text-sm text-slate-400">Sin ingredientes. Agrega el primero.</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="actualizar_costo" value="1" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                    Actualizar el costo del producto con el costo de la receta
                </label>
                <button class="btn-primary px-6 py-2.5">Guardar receta</button>
            </div>
        </form>
    </div>
    @endif

    @push('scripts')
    <script>
        function receta() {
            return {
                insumos: @js($insumosJson),
                precio: {{ (float) $producto->precio }},
                filas: @js($recetaJson->isEmpty() ? [['insumo_id'=>'','cantidad'=>1]] : $recetaJson),
                costoTotal: 0,
                margen: 0,
                init() { this.recalc(); },
                insumoDe(id) { return this.insumos.find(i => i.id == id); },
                unidadDe(id) { const i = this.insumoDe(id); return i ? i.unidad : ''; },
                subtotal(fila) { const i = this.insumoDe(fila.insumo_id); return i ? i.costo * (parseFloat(fila.cantidad) || 0) : 0; },
                recalc() {
                    this.costoTotal = this.filas.reduce((s, f) => s + this.subtotal(f), 0);
                    this.margen = this.precio > 0 ? ((this.precio - this.costoTotal) / this.precio) * 100 : 0;
                },
                agregar() { this.filas.push({ insumo_id: '', cantidad: 1 }); },
                quitar(idx) { this.filas.splice(idx, 1); this.recalc(); },
            }
        }
    </script>
    @endpush
</x-app-layout>

<x-app-layout title="Carta / Menú">
    @php $m = \App\Models\Restaurante::actual()->moneda; @endphp
    <x-page-header title="Carta / Menú" subtitle="Gestiona los platos y bebidas de tu restaurante.">
        <a href="{{ route('productos.create') }}" class="btn-primary">+ Nuevo producto</a>
    </x-page-header>

    <form method="GET" class="card mb-4 flex flex-wrap items-center gap-3">
        <input name="q" value="{{ request('q') }}" placeholder="Buscar producto..." class="form-input-c max-w-xs">
        <select name="categoria" class="form-input-c max-w-xs">
            <option value="">Todas las categorías</option>
            @foreach($categorias as $c)
                <option value="{{ $c->id }}" @selected(request('categoria')==$c->id)>{{ $c->nombre }}</option>
            @endforeach
        </select>
        <button class="btn-secondary">Filtrar</button>
    </form>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($productos as $p)
            <div class="card flex flex-col">
                <div class="mb-3 flex h-28 items-center justify-center rounded-xl bg-gradient-to-br from-brand-50 to-accent-50 text-4xl">
                    {{ $p->categoria->icono ?? '🍽️' }}
                </div>
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-bold text-slate-800">{{ $p->nombre }}</p>
                    <span class="badge {{ $p->disponible ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">{{ $p->disponible ? 'Disponible' : 'Agotado' }}</span>
                </div>
                <p class="text-xs text-slate-400">{{ $p->categoria->nombre }}</p>
                <p class="mt-2 text-lg font-extrabold text-brand-600">{{ $m }} {{ number_format($p->precio, 2) }}</p>
                <div class="mt-auto flex gap-2 pt-3">
                    <a href="{{ route('recetas.edit', $p) }}" class="flex-1 rounded-xl bg-brand-50 py-1.5 text-center text-xs font-semibold text-brand-700 ring-1 ring-brand-100 transition hover:bg-brand-100">🧾 Receta</a>
                    <a href="{{ route('productos.edit', $p) }}" class="flex-1 rounded-xl bg-white py-1.5 text-center text-xs font-semibold text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-50">Editar</a>
                    <form method="POST" action="{{ route('productos.destroy', $p) }}" onsubmit="return confirm('¿Eliminar producto?')">
                        @csrf @method('DELETE')
                        <button class="btn-danger py-1.5 text-xs">×</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="col-span-full rounded-2xl bg-white p-8 text-center text-slate-400">No se encontraron productos.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $productos->links() }}</div>
</x-app-layout>

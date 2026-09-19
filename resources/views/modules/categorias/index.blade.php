<x-app-layout title="Categorías">
    <x-page-header title="Categorías" subtitle="Organiza tu carta por secciones.">
        <a href="{{ route('categorias.create') }}" class="btn-primary">+ Nueva categoría</a>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($categorias as $cat)
            <div class="card">
                <div class="flex items-start justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl text-2xl" style="background: {{ $cat->color }}22">{{ $cat->icono ?: '🍽️' }}</div>
                    <span class="badge {{ $cat->activo ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">{{ $cat->activo ? 'Activa' : 'Inactiva' }}</span>
                </div>
                <p class="mt-3 text-base font-bold text-slate-800">{{ $cat->nombre }}</p>
                <p class="text-xs text-slate-400">{{ $cat->productos_count }} producto(s)</p>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('categorias.edit', $cat) }}" class="btn-secondary flex-1 py-1.5 text-xs">Editar</a>
                    <form method="POST" action="{{ route('categorias.destroy', $cat) }}" onsubmit="return confirm('¿Eliminar categoría?')">
                        @csrf @method('DELETE')
                        <button class="btn-danger py-1.5 text-xs">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="col-span-full rounded-2xl bg-white p-8 text-center text-slate-400">No hay categorías. Crea la primera.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $categorias->links() }}</div>
</x-app-layout>

<x-app-layout title="Mesas">
    <x-page-header title="Estado de salones" subtitle="Vista general de las mesas del restaurante.">
        <a href="{{ route('mesas.create') }}" class="btn-primary">+ Nueva mesa</a>
    </x-page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach (['libre'=>['Libres','emerald'],'ocupada'=>['Ocupadas','rose'],'reservada'=>['Reservadas','amber'],'cuenta'=>['Por cobrar','indigo']] as $k => $v)
            <div class="card flex items-center gap-3">
                <span class="h-3 w-3 rounded-full bg-{{ $v[1] }}-500"></span>
                <div>
                    <p class="text-xl font-extrabold text-slate-800">{{ $resumen[$k] }}</p>
                    <p class="text-xs text-slate-400">{{ $v[0] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    @forelse ($mesas as $zona => $items)
        <div class="card mb-4">
            <p class="mb-4 text-sm font-bold text-slate-700">{{ $zona }}</p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                @foreach ($items as $mesa)
                    @php $c = $mesa->color_estado; $labels = ['libre'=>'Libre','ocupada'=>'Ocupada','reservada'=>'Reservada','cuenta'=>'Por cobrar']; @endphp
                    <a href="{{ route('mesas.edit', $mesa) }}" class="group rounded-2xl border border-{{ $c }}-100 bg-{{ $c }}-50 p-4 text-center transition hover:shadow-md">
                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-{{ $c }}-100 text-{{ $c }}-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16"/></svg>
                        </div>
                        <p class="mt-2 text-sm font-bold text-slate-700">{{ $mesa->nombre ?? 'Mesa '.$mesa->numero }}</p>
                        <p class="text-[11px] text-slate-400">{{ $mesa->capacidad }} personas</p>
                        <span class="mt-1 inline-block text-[11px] font-semibold text-{{ $c }}-600">{{ $labels[$mesa->estado] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <p class="rounded-2xl bg-white p-8 text-center text-slate-400">No hay mesas registradas.</p>
    @endforelse
</x-app-layout>

<x-app-layout title="Usuarios">
    <x-page-header title="Usuarios" subtitle="Gestiona el acceso del personal.">
        <a href="{{ route('usuarios.create') }}" class="btn-primary">+ Nuevo usuario</a>
    </x-page-header>

    <form method="GET" class="card mb-4 flex gap-3">
        <input name="q" value="{{ request('q') }}" placeholder="Buscar usuario..." class="form-input-c max-w-md">
        <button class="btn-secondary">Buscar</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-100 text-left text-xs uppercase text-slate-400">
                <th class="py-2 font-semibold">Usuario</th><th class="py-2 font-semibold">Rol</th>
                <th class="py-2 font-semibold">Teléfono</th><th class="py-2 font-semibold">Estado</th>
                <th class="py-2 text-right font-semibold">Acciones</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($usuarios as $us)
                    @php $rc = ['admin'=>'brand','cajero'=>'sky','mesero'=>'emerald','cocina'=>'amber'][$us->role] ?? 'slate'; @endphp
                    <tr>
                        <td class="py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">{{ $us->iniciales }}</span>
                                <div><p class="font-semibold text-slate-700">{{ $us->name }}</p><p class="text-xs text-slate-400">{{ $us->email }}</p></div>
                            </div>
                        </td>
                        <td class="py-3"><span class="badge bg-{{ $rc }}-50 text-{{ $rc }}-600">{{ $us->role_label }}</span></td>
                        <td class="py-3 text-slate-600">{{ $us->telefono ?: '—' }}</td>
                        <td class="py-3"><span class="badge {{ $us->activo ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">{{ $us->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        <td class="py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('usuarios.edit', $us) }}" class="btn-secondary py-1 text-xs">Editar</a>
                                @if($us->id !== auth()->id())
                                <form method="POST" action="{{ route('usuarios.destroy', $us) }}" onsubmit="return confirm('¿Eliminar usuario?')">
                                    @csrf @method('DELETE')<button class="btn-danger py-1 text-xs">×</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-slate-400">No hay usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $usuarios->links() }}</div>
</x-app-layout>

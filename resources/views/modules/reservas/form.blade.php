<x-app-layout :title="$reserva->exists ? 'Editar reserva' : 'Nueva reserva'">
    <x-page-header :title="$reserva->exists ? 'Editar reserva' : 'Nueva reserva'">
        <a href="{{ route('reservas.index') }}" class="btn-secondary">← Volver</a>
    </x-page-header>

    <div class="card max-w-2xl">
        <x-val-errors />
        <form method="POST" action="{{ $reserva->exists ? route('reservas.update', $reserva) : route('reservas.store') }}" class="space-y-5">
            @csrf
            @if($reserva->exists) @method('PUT') @endif
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre del cliente *</label>
                    <input name="nombre_cliente" value="{{ old('nombre_cliente', $reserva->nombre_cliente) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono', $reserva->telefono) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Cliente registrado (opcional)</label>
                    <select name="cliente_id" class="form-input-c">
                        <option value="">— Sin asociar —</option>
                        @foreach($clientes as $cl)<option value="{{ $cl->id }}" @selected(old('cliente_id', $reserva->cliente_id)==$cl->id)>{{ $cl->nombre }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Mesa (opcional)</label>
                    <select name="mesa_id" class="form-input-c">
                        <option value="">— Asignar luego —</option>
                        @foreach($mesas as $ms)<option value="{{ $ms->id }}" @selected(old('mesa_id', $reserva->mesa_id)==$ms->id)>{{ $ms->nombre ?? 'Mesa '.$ms->numero }} ({{ $ms->capacidad }}p)</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Fecha *</label>
                    <input type="date" name="fecha" value="{{ old('fecha', $reserva->fecha?->toDateString() ?? $reserva->fecha) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Hora *</label>
                    <input type="time" name="hora" value="{{ old('hora', \Illuminate\Support\Str::of($reserva->hora)->substr(0,5)) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Personas *</label>
                    <input type="number" name="personas" value="{{ old('personas', $reserva->personas ?? 2) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Estado *</label>
                    <select name="estado" class="form-input-c" required>
                        @foreach (['pendiente'=>'Pendiente','confirmada'=>'Confirmada','cumplida'=>'Cumplida','cancelada'=>'Cancelada'] as $k=>$v)
                            <option value="{{ $k }}" @selected(old('estado', $reserva->estado)==$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Notas</label>
                    <textarea name="notas" rows="2" class="form-input-c">{{ old('notas', $reserva->notas) }}</textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('reservas.index') }}" class="btn-secondary">Cancelar</a>
                <button class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</x-app-layout>

<x-app-layout title="Configuración">
    <x-page-header title="Configuración del restaurante" subtitle="Datos generales y parámetros del negocio." />

    <div class="card max-w-3xl">
        <x-val-errors />
        <form method="POST" action="{{ route('configuracion.update') }}" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre del restaurante *</label>
                    <input name="nombre_restaurante" value="{{ old('nombre_restaurante', $config->nombre_restaurante) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">RUC</label>
                    <input name="ruc" value="{{ old('ruc', $config->ruc) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono', $config->telefono) }}" class="form-input-c">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Dirección</label>
                    <input name="direccion" value="{{ old('direccion', $config->direccion) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $config->email) }}" class="form-input-c">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Moneda *</label>
                    <input name="moneda" value="{{ old('moneda', $config->moneda) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">IGV / Impuesto (%) *</label>
                    <input type="number" step="0.01" name="igv" value="{{ old('igv', $config->igv) }}" class="form-input-c" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Meta mensual *</label>
                    <input type="number" step="0.01" name="meta_mensual" value="{{ old('meta_mensual', $config->meta_mensual) }}" class="form-input-c" required>
                </div>
            </div>
            <div class="flex justify-end">
                <button class="btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</x-app-layout>

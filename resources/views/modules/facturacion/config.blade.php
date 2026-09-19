<x-app-layout title="Configuración de facturación">
    <x-page-header title="Facturación electrónica — Configuración"
        subtitle="Datos del emisor, credenciales SUNAT, certificado digital y series de tu empresa." />

    @include('modules.facturacion._banner')

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="card max-w-3xl">
        <x-val-errors />
        <form method="POST" action="{{ route('facturacion.config.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf @method('PUT')

            {{-- ===== Estado ===== --}}
            <label class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3">
                <input type="checkbox" name="fe_activo" value="1" @checked(old('fe_activo', $rest->fe_activo)) class="h-5 w-5 rounded border-slate-300 text-brand-600">
                <span>
                    <span class="block text-sm font-semibold text-slate-800">Facturación electrónica activa</span>
                    <span class="block text-xs text-slate-500">Habilita la emisión de comprobantes ante SUNAT para esta empresa.</span>
                </span>
            </label>

            {{-- ===== Datos del emisor ===== --}}
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">Datos del emisor</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Razón social *</label>
                        <input name="nombre" value="{{ old('nombre', $rest->nombre) }}" class="form-input-c" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">RUC</label>
                        <input name="ruc" value="{{ old('ruc', $rest->ruc) }}" class="form-input-c" maxlength="11" placeholder="20000000001">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Ubigeo</label>
                        <input name="ubigeo" value="{{ old('ubigeo', $rest->ubigeo) }}" class="form-input-c" maxlength="6" placeholder="150101">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Dirección fiscal</label>
                        <input name="direccion" value="{{ old('direccion', $rest->direccion) }}" class="form-input-c">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Moneda (ISO) *</label>
                        <input name="moneda" value="{{ old('moneda', in_array($rest->moneda, ['PEN','USD','EUR']) ? $rest->moneda : 'PEN') }}" class="form-input-c" placeholder="PEN" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">IGV (%) *</label>
                        <input type="number" step="0.01" name="igv" value="{{ old('igv', $rest->igv) }}" class="form-input-c" required>
                    </div>
                </div>
            </div>

            {{-- ===== Credenciales SUNAT ===== --}}
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">Credenciales SUNAT</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Ambiente *</label>
                        <select name="sunat_ambiente" class="form-input-c" required>
                            <option value="beta" @selected(old('sunat_ambiente', $rest->sunat_ambiente) === 'beta')>Pruebas (beta)</option>
                            <option value="produccion" @selected(old('sunat_ambiente', $rest->sunat_ambiente) === 'produccion')>Producción</option>
                        </select>
                    </div>
                    <div></div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Usuario SOL</label>
                        <input name="sunat_sol_user" value="{{ old('sunat_sol_user', $rest->sunat_sol_user) }}" class="form-input-c" placeholder="MODDATOS">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Clave SOL</label>
                        <input type="password" name="sunat_sol_pass" class="form-input-c" placeholder="{{ $rest->sunat_sol_pass ? '•••••• (dejar en blanco para no cambiar)' : 'clave SOL' }}">
                    </div>
                </div>
            </div>

            {{-- ===== Certificado ===== --}}
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">Certificado digital</h3>
                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Archivo del certificado (.pem)</label>
                        <input type="file" name="certificado" accept=".pem,.pfx,.p12" class="form-input-c">
                        <p class="mt-1 text-xs text-slate-500">
                            @if($rest->sunat_cert_path)
                                Certificado actual: <span class="font-mono text-slate-600">{{ $rest->sunat_cert_path }}</span>. Sube uno nuevo para reemplazarlo.
                            @else
                                Aún no se ha cargado un certificado. En pruebas puedes usar el certificado beta de Greenter.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- ===== Series ===== --}}
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">Series y correlativos</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="mb-3 text-sm font-semibold text-slate-700">Factura</p>
                        <label class="mb-1 block text-xs text-slate-500">Serie</label>
                        <input name="factura_serie" value="{{ old('factura_serie', $serieFactura->serie ?? 'F001') }}" class="form-input-c mb-3">
                        <label class="mb-1 block text-xs text-slate-500">Correlativo actual</label>
                        <input type="number" min="0" name="factura_corr" value="{{ old('factura_corr', $serieFactura->correlativo_actual ?? 0) }}" class="form-input-c">
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="mb-3 text-sm font-semibold text-slate-700">Boleta</p>
                        <label class="mb-1 block text-xs text-slate-500">Serie</label>
                        <input name="boleta_serie" value="{{ old('boleta_serie', $serieBoleta->serie ?? 'B001') }}" class="form-input-c mb-3">
                        <label class="mb-1 block text-xs text-slate-500">Correlativo actual</label>
                        <input type="number" min="0" name="boleta_corr" value="{{ old('boleta_corr', $serieBoleta->correlativo_actual ?? 0) }}" class="form-input-c">
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-500">El siguiente comprobante usará el correlativo actual + 1.</p>
            </div>

            <div class="flex justify-end">
                <button class="btn-primary">Guardar configuración</button>
            </div>
        </form>
    </div>
</x-app-layout>

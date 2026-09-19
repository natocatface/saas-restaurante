<x-superadmin-layout title="Mi perfil">
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-800">Mi perfil</h1>
        <p class="mt-1 text-sm text-slate-500">Administra tus datos de Super Administrador.</p>
    </div>

    <x-val-errors />

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Tarjeta de identidad --}}
        <div class="card h-fit text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-brand-500 to-accent-500 text-2xl font-extrabold text-white shadow-lg shadow-brand-600/30">{{ $user->iniciales }}</div>
            <p class="mt-4 text-lg font-extrabold text-slate-800">{{ $user->name }}</p>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
            <span class="badge mt-3 bg-brand-50 text-brand-700">🛡️ {{ $user->role_label }}</span>
        </div>

        <div class="space-y-4 lg:col-span-2">
            {{-- Datos --}}
            <div class="card">
                <p class="mb-4 text-sm font-bold text-slate-700">Datos personales</p>
                <form method="POST" action="{{ route('superadmin.profile.update') }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre</label>
                            <input name="name" value="{{ old('name', $user->name) }}" class="form-input-c" required>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Correo electrónico</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input-c" required>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Teléfono</label>
                            <input name="telefono" value="{{ old('telefono', $user->telefono) }}" class="form-input-c" placeholder="Opcional">
                        </div>
                    </div>
                    <div class="flex justify-end"><button class="btn-primary">Guardar cambios</button></div>
                </form>
            </div>

            {{-- Contraseña --}}
            <div class="card">
                <p class="mb-4 text-sm font-bold text-slate-700">Cambiar contraseña</p>
                <form method="POST" action="{{ route('superadmin.profile.password') }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Contraseña actual</label>
                        <input type="password" name="current_password" class="form-input-c" required autocomplete="current-password">
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nueva contraseña</label>
                            <input type="password" name="password" class="form-input-c" required autocomplete="new-password">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" class="form-input-c" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="flex justify-end"><button class="btn-primary">Actualizar contraseña</button></div>
                </form>
            </div>
        </div>
    </div>
</x-superadmin-layout>

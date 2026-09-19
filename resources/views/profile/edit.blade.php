<x-app-layout title="Mi perfil">
    <x-page-header title="Mi perfil" subtitle="Actualiza tu información personal y contraseña." />

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Datos --}}
        <div class="card">
            <p class="mb-4 text-sm font-bold text-slate-700">Información personal</p>
            @if (session('status') === 'profile-updated')
                <p class="mb-3 text-sm font-medium text-emerald-600">Perfil actualizado.</p>
            @endif
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre</label>
                    <input name="name" value="{{ old('name', $user->name) }}" class="form-input-c" required>
                    @error('name') <p class="mt-1 text-sm text-accent-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input-c" required>
                    @error('email') <p class="mt-1 text-sm text-accent-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono', $user->telefono) }}" class="form-input-c">
                </div>
                <button class="btn-primary">Guardar</button>
            </form>
        </div>

        {{-- Contraseña --}}
        <div class="card">
            <p class="mb-4 text-sm font-bold text-slate-700">Cambiar contraseña</p>
            @if (session('status') === 'password-updated')
                <p class="mb-3 text-sm font-medium text-emerald-600">Contraseña actualizada.</p>
            @endif
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Contraseña actual</label>
                    <input type="password" name="current_password" class="form-input-c">
                    @error('current_password', 'updatePassword') <p class="mt-1 text-sm text-accent-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nueva contraseña</label>
                    <input type="password" name="password" class="form-input-c">
                    @error('password', 'updatePassword') <p class="mt-1 text-sm text-accent-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirmation" class="form-input-c">
                </div>
                <button class="btn-primary">Actualizar contraseña</button>
            </form>
        </div>
    </div>
</x-app-layout>

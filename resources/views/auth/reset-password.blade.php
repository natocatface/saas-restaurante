<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-800">Nueva contraseña</h2>
        <p class="mt-1 text-sm text-slate-500">Define una nueva contraseña para tu cuenta.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                   class="form-input-c">
            @error('email') <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Contraseña</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="form-input-c">
            @error('password') <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-input-c">
        </div>

        <button type="submit" class="btn-primary w-full py-3">Restablecer contraseña</button>
    </form>
</x-guest-layout>

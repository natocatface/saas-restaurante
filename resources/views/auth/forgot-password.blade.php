<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-800">Recuperar contraseña</h2>
        <p class="mt-1 text-sm text-slate-500">Te enviaremos un enlace para restablecer tu contraseña.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="form-input-c" placeholder="tucorreo@restaurante.com">
            @error('email') <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn-primary w-full py-3">Enviar enlace de recuperación</button>
        <p class="text-center text-sm text-slate-500">
            <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Volver a iniciar sesión</a>
        </p>
    </form>
</x-guest-layout>

<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-800">Crea tu restaurante</h2>
        <p class="mt-1 text-sm text-slate-500">14 días de prueba gratis. Sin tarjeta de crédito.</p>
    </div>

    @if ($planSeleccionado)
        <div class="mb-5 flex items-center gap-3 rounded-xl bg-brand-50 px-4 py-3 text-sm">
            <span class="text-brand-600">●</span>
            <span class="text-slate-600">Plan seleccionado: <strong class="text-brand-700">{{ $planSeleccionado->nombre }}</strong> — S/ {{ number_format($planSeleccionado->precio, 0) }}/{{ $planSeleccionado->intervalo === 'anual' ? 'año' : 'mes' }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="plan" value="{{ $planSeleccionado->slug ?? old('plan') }}">

        <div>
            <label for="restaurante" class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre del restaurante</label>
            <input id="restaurante" type="text" name="restaurante" value="{{ old('restaurante') }}" required autofocus
                   class="form-input-c" placeholder="Ej. Sabores del Perú">
            @error('restaurante') <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="name" class="mb-1.5 block text-sm font-semibold text-slate-700">Tu nombre (administrador)</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                   class="form-input-c" placeholder="Tu nombre completo">
            @error('name') <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Correo</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                       class="form-input-c" placeholder="correo@restaurante.com">
                @error('email') <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="telefono" class="mb-1.5 block text-sm font-semibold text-slate-700">Teléfono</label>
                <input id="telefono" type="text" name="telefono" value="{{ old('telefono') }}"
                       class="form-input-c" placeholder="9XX XXX XXX">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" class="form-input-c" placeholder="••••••••">
                @error('password') <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Confirmar</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-input-c" placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="btn-primary w-full py-3">Crear mi restaurante</button>

        <p class="text-center text-sm text-slate-500">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Inicia sesión</a>
        </p>
    </form>
</x-guest-layout>

<x-guest-layout>
    <div x-data="{ set(e,p){ document.getElementById('email').value=e; document.getElementById('password').value=p; } }">
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900">Bienvenido de vuelta 👋</h2>
            <p class="mt-1.5 text-sm text-slate-500">Ingresa tus credenciales para acceder al sistema</p>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Correo electrónico</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="form-input-c pl-11" placeholder="tucorreo@restaurante.com">
                </div>
                @error('email') <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Contraseña</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 11h14a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2z"/></svg>
                    </span>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="form-input-c pl-11" placeholder="••••••••">
                </div>
                @error('password') <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="flex items-center gap-2 text-sm text-slate-600">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                    Recordarme
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 py-3.5 text-base font-bold text-white shadow-lg shadow-brand-600/30 transition hover:opacity-95">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l4-4m0 0l-4-4m4 4H3m4 8h8a2 2 0 002-2V6a2 2 0 00-2-2H7"/></svg>
                Iniciar sesión
            </button>

            @if (Route::has('register'))
                <p class="text-center text-sm text-slate-500">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Crear una cuenta</a>
                </p>
            @endif
        </form>

        {{-- Cuentas de demostración --}}
        <div class="mt-8">
            <div class="relative text-center">
                <span class="relative bg-slate-50 px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Cuentas de demostración</span>
                <div class="absolute left-0 right-0 top-1/2 -z-0 border-t border-slate-200"></div>
            </div>

            <div class="mt-4 rounded-2xl border border-dashed border-brand-200 bg-white p-3">
                <p class="mb-2 px-1 text-xs font-bold text-brand-700">🔑 ACCESO RÁPIDO <span class="font-normal text-slate-400">(clic para autocompletar)</span></p>
                <button type="button" @click="set('admin@restaurante.test','password')" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left transition hover:bg-brand-50">
                    <span class="font-mono text-sm text-slate-600">admin@restaurante.test</span>
                    <span class="badge bg-brand-100 text-brand-700">Admin</span>
                </button>
                <button type="button" @click="set('super@saas.test','password')" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left transition hover:bg-brand-50">
                    <span class="font-mono text-sm text-slate-600">super@saas.test</span>
                    <span class="badge bg-emerald-100 text-emerald-700">Super Admin</span>
                </button>
            </div>
        </div>

        <p class="mt-8 text-center text-xs text-slate-400">© {{ date('Y') }} Mi Restaurante VIP · Todos los derechos reservados.</p>
    </div>
</x-guest-layout>

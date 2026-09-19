@props(['title' => null])
@php
    $u = auth()->user();
    $nav = [
        ['superadmin.dashboard',    'Dashboard',    'superadmin.dashboard',  'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['superadmin.restaurantes.index', 'Restaurantes', 'superadmin.restaurantes.*', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['superadmin.planes.index', 'Planes',       'superadmin.planes.*',   'M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z'],
        ['superadmin.profile.edit', 'Mi perfil',    'superadmin.profile.*',  'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}Super Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100" x-data="{ sidebarOpen: false }">
<div class="min-h-screen lg:flex">

    <div x-show="sidebarOpen" @click="sidebarOpen=false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" style="display:none"></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col overflow-hidden bg-gradient-to-b from-slate-950 to-slate-900 transition-transform duration-200 lg:static lg:translate-x-0">
        <div class="pointer-events-none absolute -left-10 -top-10 h-48 w-48 rounded-full bg-brand-600/20 blur-3xl"></div>

        <div class="relative flex items-center gap-3 px-6 py-5">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-accent-500 text-xl shadow-lg shadow-brand-900/40">🛡️</div>
            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold text-white">Panel <span class="text-brand-400">SaaS</span></p>
                <p class="text-[11px] text-slate-400">Super Administrador</p>
            </div>
        </div>

        <nav class="relative flex-1 space-y-1 px-4 pt-4">
            @foreach ($nav as [$route, $label, $pattern, $icon])
                <a href="{{ route($route) }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition {{ request()->routeIs($pattern) ? 'bg-gradient-to-r from-brand-600 to-brand-500 text-white shadow-lg shadow-brand-900/40' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>

        <div class="relative border-t border-white/10 p-4">
            <div class="flex items-center gap-3 rounded-xl bg-white/5 px-3 py-2.5">
                <a href="{{ route('superadmin.profile.edit') }}" class="flex min-w-0 flex-1 items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-accent-500 text-sm font-bold text-white">{{ $u->iniciales }}</div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ $u->name }}</p>
                        <p class="text-[11px] text-slate-400">{{ $u->role_label }}</p>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Cerrar sesión" class="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-slate-200 bg-white/80 px-4 py-3 backdrop-blur sm:px-6">
            <button @click="sidebarOpen=true" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="min-w-0">
                <p class="text-sm font-bold text-slate-700">Administración del SaaS</p>
                <p class="hidden text-xs text-slate-400 sm:block">Gestiona restaurantes, planes y suscripciones</p>
            </div>
            <a href="{{ route('superadmin.planes.create') }}" class="btn-primary ml-auto">+ Nuevo plan</a>
        </header>

        @if (session('success'))
            <div class="mx-4 mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 sm:mx-6">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mx-4 mt-4 rounded-xl bg-accent-50 px-4 py-3 text-sm font-medium text-accent-700 sm:mx-6">{{ session('error') }}</div>
        @endif

        <main class="flex-1 p-4 sm:p-6">
            {{ $slot }}
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>

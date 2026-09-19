@props(['title', 'subtitle' => null])
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-800">{{ $title }}</h1>
        @if($subtitle)<p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>@endif
    </div>
    <div class="flex items-center gap-2">{{ $slot }}</div>
</div>

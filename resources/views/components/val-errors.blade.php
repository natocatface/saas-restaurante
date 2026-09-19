@if ($errors->any())
    <div class="mb-4 rounded-xl bg-accent-50 px-4 py-3 text-sm text-accent-700">
        <p class="font-semibold">Revisa los siguientes campos:</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

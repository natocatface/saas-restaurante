<x-app-layout title="Carta QR">
    <x-page-header title="Carta digital QR" subtitle="Comparte tu carta con un código QR en cada mesa.">
        <a href="{{ $url }}" target="_blank" class="btn-secondary">👁️ Ver carta</a>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Tarjeta del QR --}}
        <div class="card text-center" x-data="qrbox()">
            <div id="qr-print" class="mx-auto inline-block rounded-2xl bg-white p-6">
                <p class="mb-1 text-lg font-extrabold text-slate-800">{{ $restaurante->nombre }}</p>
                <p class="mb-4 text-sm text-slate-500">Escanea para ver nuestra carta 🍽️</p>
                <div id="qrcode" class="mx-auto flex w-fit justify-center"></div>
                <p class="mt-4 text-xs text-slate-400">{{ parse_url($url, PHP_URL_HOST) }}</p>
            </div>
            <div class="mt-5 flex flex-wrap justify-center gap-2 print:hidden">
                <button @click="descargar()" class="btn-primary">⬇️ Descargar PNG</button>
                <button onclick="window.print()" class="btn-secondary">🖨️ Imprimir</button>
            </div>
        </div>

        {{-- Info / enlace --}}
        <div class="space-y-6">
            <div class="card">
                <h3 class="font-bold text-slate-800">Enlace público</h3>
                <p class="mt-1 text-sm text-slate-500">Cualquiera con este enlace puede ver tu carta, sin iniciar sesión.</p>
                <div class="mt-3 flex gap-2" x-data="{ url: @js($url), copiado: false, copiar(){ navigator.clipboard.writeText(this.url); this.copiado=true; setTimeout(()=>this.copiado=false,1500); } }">
                    <input type="text" readonly :value="url" class="form-input-c bg-slate-50 text-sm">
                    <button @click="copiar()" class="btn-secondary whitespace-nowrap" x-text="copiado ? '✓ Copiado' : 'Copiar'"></button>
                </div>
            </div>

            <div class="card">
                <h3 class="font-bold text-slate-800">¿Cómo usarlo?</h3>
                <ol class="mt-3 space-y-2 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="font-bold text-brand-600">1.</span> Descarga o imprime el código QR.</li>
                    <li class="flex gap-2"><span class="font-bold text-brand-600">2.</span> Colócalo en las mesas, la entrada o la carta física.</li>
                    <li class="flex gap-2"><span class="font-bold text-brand-600">3.</span> El cliente escanea con su celular y ve la carta actualizada al instante.</li>
                </ol>
                <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700">💡 La carta muestra solo categorías activas y productos disponibles. Cambios en tu menú se reflejan automáticamente.</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        function qrbox() {
            return {
                init() {
                    new QRCode(document.getElementById('qrcode'), {
                        text: @js($url),
                        width: 220, height: 220,
                        colorDark: '#ea580c', colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H,
                    });
                },
                descargar() {
                    const cv = document.querySelector('#qrcode canvas');
                    const img = document.querySelector('#qrcode img');
                    const data = cv ? cv.toDataURL('image/png') : (img ? img.src : null);
                    if (!data) return;
                    const a = document.createElement('a');
                    a.href = data; a.download = 'carta-qr-{{ $restaurante->slug }}.png'; a.click();
                },
            }
        }
        document.addEventListener('alpine:init', () => {});
    </script>
    <style>@media print { body * { visibility: hidden } #qr-print, #qr-print * { visibility: visible } #qr-print { position: absolute; left: 50%; top: 80px; transform: translateX(-50%) } }</style>
    @endpush
</x-app-layout>

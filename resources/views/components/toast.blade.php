{{-- @author Ayrtonalania --}}
{{-- Toast de notificación fijo en pantalla.
     Uso: <x-toast /> — lee automáticamente session('success'), session('error'), session('warning').
     Persistente: se descarta únicamente al pulsar el botón de cierre. --}}

@if(session('success') || session('error') || session('warning'))
<div
    x-data="{ show: true }"
    x-init="setTimeout(() => show = false, 5000)"
    x-show="show"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
    class="fixed bottom-6 right-4 sm:right-6 z-50 w-full max-w-sm"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
>
    {{-- ── Éxito ── --}}
    @if(session('success'))
    @php
        $parts = array_filter(array_map('trim', explode('·', session('success'))));
    @endphp
    <div class="flex items-start gap-3
                bg-white dark:bg-gray-800
                border border-emerald-300 dark:border-emerald-700
                shadow-xl rounded-xl px-4 py-4">
        <div class="flex-shrink-0 w-9 h-9 rounded-full bg-emerald-100 dark:bg-emerald-900/40
                    flex items-center justify-center mt-0.5">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                Configuración guardada
            </p>
            @if(count($parts) > 1)
                <ul class="mt-1.5 space-y-0.5">
                    @foreach($parts as $part)
                        <li class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                            <span class="w-1 h-1 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                            {{ $part }}
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ session('success') }}
                </p>
            @endif
        </div>
        <button @click="show = false"
                aria-label="Cerrar notificación"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                       focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2
                       dark:focus:ring-offset-gray-800 rounded-md p-1 transition-colors mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    {{-- ── Error ── --}}
    @if(session('error'))
    <div class="flex items-start gap-3
                bg-white dark:bg-gray-800
                border border-red-300 dark:border-red-700
                shadow-xl rounded-xl px-4 py-4">
        <div class="flex-shrink-0 w-9 h-9 rounded-full bg-red-100 dark:bg-red-900/40
                    flex items-center justify-center mt-0.5">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor"
                 viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-red-800 dark:text-red-300">Ha ocurrido un error</p>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ session('error') }}</p>
        </div>
        <button @click="show = false"
                aria-label="Cerrar notificación"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                       focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2
                       dark:focus:ring-offset-gray-800 rounded-md p-1 transition-colors mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    {{-- ── Aviso ── --}}
    @if(session('warning'))
    <div class="flex items-start gap-3
                bg-white dark:bg-gray-800
                border border-amber-300 dark:border-amber-700
                shadow-xl rounded-xl px-4 py-4">
        <div class="flex-shrink-0 w-9 h-9 rounded-full bg-amber-100 dark:bg-amber-900/40
                    flex items-center justify-center mt-0.5">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor"
                 viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Advertencia</p>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ session('warning') }}</p>
        </div>
        <button @click="show = false"
                aria-label="Cerrar notificación"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                       focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2
                       dark:focus:ring-offset-gray-800 rounded-md p-1 transition-colors mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

</div>
@endif

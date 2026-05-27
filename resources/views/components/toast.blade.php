{{-- @author Ayrtonalania --}}
{{-- Toast de notificación fijo en pantalla.
     Uso: <x-toast /> — lee automáticamente session('success'), session('error'), session('warning').
     Siempre visible independientemente del scroll. Se auto-descarta a los 5 segundos. --}}

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
    @if(session('success'))
    <div class="flex items-start gap-3
                bg-white dark:bg-gray-800
                border border-green-300 dark:border-green-700
                shadow-lg rounded-xl px-4 py-3.5">
        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/40
                    flex items-center justify-center mt-0.5">
            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0 pt-0.5">
            <p class="text-sm font-semibold text-green-800 dark:text-green-300">Cambios guardados</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ session('success') }}</p>
        </div>
        <button @click="show = false"
                aria-label="Cerrar notificación"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                       focus:outline-none focus:ring-2 focus:ring-green-400 rounded-md p-0.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-start gap-3
                bg-white dark:bg-gray-800
                border border-red-300 dark:border-red-700
                shadow-lg rounded-xl px-4 py-3.5">
        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/40
                    flex items-center justify-center mt-0.5">
            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0 pt-0.5">
            <p class="text-sm font-semibold text-red-800 dark:text-red-300">Ha ocurrido un error</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ session('error') }}</p>
        </div>
        <button @click="show = false"
                aria-label="Cerrar notificación"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                       focus:outline-none focus:ring-2 focus:ring-red-400 rounded-md p-0.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    @if(session('warning'))
    <div class="flex items-start gap-3
                bg-white dark:bg-gray-800
                border border-amber-300 dark:border-amber-700
                shadow-lg rounded-xl px-4 py-3.5">
        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/40
                    flex items-center justify-center mt-0.5">
            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0 pt-0.5">
            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Advertencia</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ session('warning') }}</p>
        </div>
        <button @click="show = false"
                aria-label="Cerrar notificación"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                       focus:outline-none focus:ring-2 focus:ring-amber-400 rounded-md p-0.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

</div>
@endif

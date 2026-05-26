{{-- @author SebastianBCF --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center h-9 w-9 rounded-lg
                        bg-indigo-100 dark:bg-indigo-900/40">
                <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                     aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0
                             01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Configuración del ticket PDF') }}
            </h2>
        </div>
    </x-slot>

    {{-- div en lugar de main: el layout x-app-layout ya envuelve en <main id="main-content"> --}}
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ─── FLASH ÉXITO: triple redundancia icono + color + texto ─── --}}
            @if(session('success'))
                <div role="alert"
                     aria-live="polite"
                     class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20
                            border border-emerald-200 dark:border-emerald-700
                            p-4 flex items-start gap-3">
                    <div class="flex items-center justify-center h-8 w-8 rounded-full
                                bg-emerald-100 dark:bg-emerald-800/50 shrink-0">
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                             aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="pt-0.5">
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">
                            Guardado correctamente
                        </p>
                        <p class="text-sm text-emerald-700 dark:text-emerald-400 mt-0.5">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- ─── ERRORES GLOBALES ─── --}}
            @if($errors->any())
                <div role="alert"
                     aria-live="assertive"
                     class="rounded-lg bg-red-50 dark:bg-red-900/20
                            border border-red-200 dark:border-red-700
                            p-4 flex items-start gap-3">
                    <div class="flex items-center justify-center h-8 w-8 rounded-full
                                bg-red-100 dark:bg-red-800/50 shrink-0">
                        <svg class="h-4 w-4 text-red-600 dark:text-red-400"
                             aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div class="pt-0.5">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">
                            Corrige los siguientes errores antes de guardar:
                        </p>
                        <ul class="mt-1 text-sm text-red-700 dark:text-red-400
                                   list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('ticket-config.update') }}"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf
                @method('PUT')

                {{-- ═══════════════════════════════════════════════════
                     SECCIÓN: LOGO
                     ═══════════════════════════════════════════════════ --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                ring-1 ring-gray-200 dark:ring-gray-700"
                         aria-labelledby="section-logo">

                    {{-- Cabecera de sección con icono + acento de color --}}
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700
                                flex items-center gap-3">
                        <div class="flex items-center justify-center h-9 w-9 rounded-lg
                                    bg-violet-100 dark:bg-violet-900/40 shrink-0">
                            <svg class="h-5 w-5 text-violet-600 dark:text-violet-400"
                                 aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2
                                         0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0
                                         00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="section-logo"
                                class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Logo del negocio
                            </h3>
                            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                Se mostrará en la cabecera del ticket. JPEG, PNG, WebP · máx. 2&thinsp;MB.
                            </p>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:p-6 space-y-4">
                        {{-- Logo actual --}}
                        @if($ticketConfig->hasLogo())
                            <div class="flex items-center gap-4 p-3 rounded-lg
                                        bg-gray-50 dark:bg-gray-700/50
                                        border border-gray-200 dark:border-gray-600">
                                <img
                                    src="{{ $ticketConfig->getLogoUrl() }}"
                                    alt="Logo actual de {{ Auth::user()->business_name ?? Auth::user()->name }}"
                                    class="h-16 w-auto rounded-md border border-gray-200
                                           dark:border-gray-600 object-contain bg-white
                                           dark:bg-gray-900 p-1"
                                >
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Logo actual
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Sube uno nuevo para reemplazarlo.
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{-- Input de archivo --}}
                        <div>
                            <label for="logo"
                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $ticketConfig->hasLogo() ? 'Reemplazar logo' : 'Subir logo' }}
                            </label>
                            <div class="relative">
                                <input
                                    type="file"
                                    id="logo"
                                    name="logo"
                                    accept="image/jpeg,image/png,image/jpg,image/webp"
                                    aria-invalid="{{ $errors->has('logo') ? 'true' : 'false' }}"
                                    aria-describedby="logo-hint{{ $errors->has('logo') ? ' logo-error' : '' }}"
                                    class="block w-full text-sm text-gray-700 dark:text-gray-300
                                           file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                           file:text-sm file:font-medium
                                           file:bg-violet-50 file:text-violet-700
                                           dark:file:bg-violet-900/30 dark:file:text-violet-300
                                           hover:file:bg-violet-100 dark:hover:file:bg-violet-900/50
                                           focus:outline-none focus:ring-2 focus:ring-violet-500
                                           focus:ring-offset-2 dark:focus:ring-offset-gray-800 rounded-lg
                                           @error('logo') ring-2 ring-red-500 @enderror"
                                >
                            </div>
                            <p id="logo-hint" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                JPEG, PNG, WebP · máximo 2&thinsp;MB
                            </p>
                            @error('logo')
                                <p id="logo-error" role="alert"
                                   class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="h-4 w-4 shrink-0" aria-hidden="true" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- ═══════════════════════════════════════════════════
                     SECCIÓN: DATOS FISCALES
                     ═══════════════════════════════════════════════════ --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                ring-1 ring-gray-200 dark:ring-gray-700"
                         aria-labelledby="section-fiscal">

                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700
                                flex items-center gap-3">
                        <div class="flex items-center justify-center h-9 w-9 rounded-lg
                                    bg-sky-100 dark:bg-sky-900/40 shrink-0">
                            <svg class="h-5 w-5 text-sky-600 dark:text-sky-400"
                                 aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2
                                         0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5
                                         10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="section-fiscal"
                                class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Datos fiscales
                            </h3>
                            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                El nombre del negocio se toma de tu perfil
                                ({{ Auth::user()->business_name ?? Auth::user()->name }}).
                            </p>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <div class="max-w-sm">
                            <label for="tax_id"
                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                NIF / CIF del negocio
                            </label>
                            <div class="mt-1 relative">
                                {{-- Icono decorativo dentro del input --}}
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500"
                                         aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7
                                                 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828
                                                 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    id="tax_id"
                                    name="tax_id"
                                    value="{{ old('tax_id', $ticketConfig->tax_id) }}"
                                    maxlength="20"
                                    placeholder="Ej: B12345678"
                                    aria-invalid="{{ $errors->has('tax_id') ? 'true' : 'false' }}"
                                    aria-describedby="tax_id-hint{{ $errors->has('tax_id') ? ' tax_id-error' : '' }}"
                                    class="block w-full pl-9 rounded-lg border-gray-300
                                           dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100
                                           shadow-sm text-base sm:text-sm
                                           focus:border-sky-500 focus:ring-sky-500
                                           focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                           @error('tax_id') border-red-500 @enderror"
                                >
                            </div>
                            <p id="tax_id-hint" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                Formato: B12345678 · máximo 20 caracteres
                            </p>
                            @error('tax_id')
                                <p id="tax_id-error" role="alert"
                                   class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="h-4 w-4 shrink-0" aria-hidden="true" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- ═══════════════════════════════════════════════════
                     SECCIÓN: PIE DEL TICKET
                     ═══════════════════════════════════════════════════ --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                ring-1 ring-gray-200 dark:ring-gray-700"
                         aria-labelledby="section-footer">

                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700
                                flex items-center gap-3">
                        <div class="flex items-center justify-center h-9 w-9 rounded-lg
                                    bg-amber-100 dark:bg-amber-900/40 shrink-0">
                            <svg class="h-5 w-5 text-amber-600 dark:text-amber-400"
                                 aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                         a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="section-footer"
                                class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Pie del ticket
                            </h3>
                            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                Texto que aparece al final del ticket. Máximo 500 caracteres.
                            </p>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:p-6"
                         x-data="{ count: {{ mb_strlen(old('footer_text', $ticketConfig->footer_text ?? '')) }} }">
                        <label for="footer_text"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Texto del pie
                        </label>
                        <textarea
                            id="footer_text"
                            name="footer_text"
                            rows="3"
                            maxlength="500"
                            placeholder="Ej: Gracias por su visita. IVA incluido al 10%."
                            aria-invalid="{{ $errors->has('footer_text') ? 'true' : 'false' }}"
                            aria-describedby="footer-counter footer-hint{{ $errors->has('footer_text') ? ' footer_text-error' : '' }}"
                            x-on:input="count = $event.target.value.length"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600
                                   dark:bg-gray-700 dark:text-gray-100 shadow-sm
                                   text-base sm:text-sm
                                   focus:border-amber-500 focus:ring-amber-500
                                   focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                   @error('footer_text') border-red-500 @enderror"
                        >{{ old('footer_text', $ticketConfig->footer_text) }}</textarea>
                        <div class="mt-1.5 flex items-center justify-between gap-2">
                            <p id="footer-hint" class="text-xs text-gray-500 dark:text-gray-400">
                                Opcional. Se imprime al pie de cada ticket generado.
                            </p>
                            {{-- text-gray-600 = 6.99:1 sobre blanco → supera WCAG AA --}}
                            <p id="footer-counter"
                               aria-live="polite"
                               aria-atomic="true"
                               class="text-xs font-medium tabular-nums shrink-0 transition-colors"
                               :class="count >= 450
                                       ? (count >= 500
                                           ? 'text-red-600 dark:text-red-400'
                                           : 'text-amber-600 dark:text-amber-400')
                                       : 'text-gray-600 dark:text-gray-400'">
                                <span x-text="count"></span>/500
                            </p>
                        </div>
                        @error('footer_text')
                            <p id="footer_text-error" role="alert"
                               class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="h-4 w-4 shrink-0" aria-hidden="true" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </section>

                {{-- ═══════════════════════════════════════════════════
                     SECCIÓN: PLANTILLA
                     ═══════════════════════════════════════════════════ --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                ring-1 ring-gray-200 dark:ring-gray-700"
                         aria-labelledby="section-template">

                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700
                                flex items-center gap-3">
                        <div class="flex items-center justify-center h-9 w-9 rounded-lg
                                    bg-indigo-100 dark:bg-indigo-900/40 shrink-0">
                            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                                 aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0
                                         01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11
                                         7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0
                                         010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="section-template"
                                class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Plantilla del ticket
                            </h3>
                            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                Elige el estilo visual que se usará al generar el PDF.
                            </p>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <fieldset>
                            <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                                Selecciona una plantilla
                                <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                                <span class="sr-only">(obligatorio)</span>
                            </legend>
                            @error('template')
                                <p role="alert"
                                   class="mb-3 text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                @php
                                    $templateMeta = [
                                        'classic' => [
                                            'label'     => 'Clásica',
                                            'desc'      => 'Diseño tradicional con cabecera, línea divisora y tipografía serif.',
                                            'accent'    => 'amber',
                                            'icon_path' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                                        ],
                                        'modern' => [
                                            'label'     => 'Moderna',
                                            'desc'      => 'Diseño limpio con colores oscuros y tipografía sans-serif.',
                                            'accent'    => 'indigo',
                                            'icon_path' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                                        ],
                                        'minimal' => [
                                            'label'     => 'Minimalista',
                                            'desc'      => 'Solo texto, sin decoraciones. Ideal para impresoras térmicas.',
                                            'accent'    => 'gray',
                                            'icon_path' => 'M4 6h16M4 12h16M4 18h7',
                                        ],
                                    ];
                                    $accentClasses = [
                                        'amber'  => ['ring' => 'ring-amber-500',  'border' => 'border-amber-500',  'bg' => 'bg-amber-50 dark:bg-amber-900/20',  'icon' => 'text-amber-600 dark:text-amber-400',  'iconBg' => 'bg-amber-100 dark:bg-amber-900/40',  'check' => 'text-amber-600 dark:text-amber-400'],
                                        'indigo' => ['ring' => 'ring-indigo-500', 'border' => 'border-indigo-500', 'bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'icon' => 'text-indigo-600 dark:text-indigo-400', 'iconBg' => 'bg-indigo-100 dark:bg-indigo-900/40', 'check' => 'text-indigo-600 dark:text-indigo-400'],
                                        'gray'   => ['ring' => 'ring-gray-500',   'border' => 'border-gray-500',   'bg' => 'bg-gray-50 dark:bg-gray-700/40',     'icon' => 'text-gray-600 dark:text-gray-400',   'iconBg' => 'bg-gray-100 dark:bg-gray-700',       'check' => 'text-gray-600 dark:text-gray-400'],
                                    ];
                                @endphp

                                @foreach($templates as $value)
                                    @php
                                        $meta       = $templateMeta[$value];
                                        $accent     = $accentClasses[$meta['accent']];
                                        $isSelected = old('template', $ticketConfig->template) === $value;
                                    @endphp
                                    <label
                                        for="template_{{ $value }}"
                                        class="relative flex flex-col cursor-pointer rounded-xl border-2 p-4 gap-3
                                               transition duration-150 select-none
                                               focus-within:ring-2 focus-within:ring-offset-2
                                               dark:focus-within:ring-offset-gray-800
                                               {{ $isSelected
                                                    ? $accent['border'] . ' ' . $accent['bg'] . ' ' . $accent['ring'] . ' focus-within:ring-offset-0'
                                                    : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 focus-within:' . $accent['ring'] }}"
                                    >
                                        {{-- Cabecera de la tarjeta: icono de sección + label + radio + check --}}
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center h-8 w-8 rounded-lg shrink-0
                                                        {{ $accent['iconBg'] }}">
                                                <svg class="h-4 w-4 {{ $accent['icon'] }}"
                                                     aria-hidden="true" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="{{ $meta['icon_path'] }}"/>
                                                </svg>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $meta['label'] }}
                                                </span>
                                            </div>

                                            {{-- Radio input (visualmente oculto, reemplazado por el check) --}}
                                            <div class="shrink-0 flex items-center">
                                                <input
                                                    type="radio"
                                                    id="template_{{ $value }}"
                                                    name="template"
                                                    value="{{ $value }}"
                                                    {{ $isSelected ? 'checked' : '' }}
                                                    class="h-4 w-4 border-gray-300 dark:border-gray-500
                                                           focus:ring-2 focus:ring-offset-2
                                                           dark:focus:ring-offset-gray-800
                                                           {{ str_replace('ring-', 'text-', $accent['ring']) }}
                                                           {{ $accent['ring'] }}"
                                                >
                                            </div>
                                        </div>

                                        {{-- Descripción --}}
                                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                            {{ $meta['desc'] }}
                                        </p>

                                        {{-- Maqueta visual (decorativa) --}}
                                        @if($value === 'classic')
                                            <div aria-hidden="true"
                                                 class="rounded-lg border border-gray-200 dark:border-gray-600
                                                        bg-white dark:bg-gray-900 p-2.5 font-mono text-xs
                                                        text-gray-700 dark:text-gray-300 leading-tight">
                                                <div class="text-center font-bold tracking-wider text-gray-900 dark:text-gray-100">MI NEGOCIO</div>
                                                <div class="text-center text-gray-500 dark:text-gray-500 text-[10px]">NIF: B12345678</div>
                                                <div class="border-t border-dashed border-gray-300 dark:border-gray-600 my-1.5"></div>
                                                <div class="text-gray-500 dark:text-gray-500 text-[10px]">Mesa 3 · 14:30</div>
                                                <div class="border-t border-dashed border-gray-300 dark:border-gray-600 my-1.5"></div>
                                                <div class="flex justify-between text-[10px]"><span>Agua</span><span>1,50 €</span></div>
                                                <div class="flex justify-between text-[10px]"><span>Pizza</span><span>12,00 €</span></div>
                                                <div class="border-t border-dashed border-gray-300 dark:border-gray-600 my-1.5"></div>
                                                <div class="flex justify-between font-bold text-[11px]"><span>TOTAL</span><span>13,50 €</span></div>
                                                <div class="text-center text-gray-500 dark:text-gray-500 text-[10px] mt-1.5">Gracias por su visita.</div>
                                            </div>
                                        @elseif($value === 'modern')
                                            <div aria-hidden="true"
                                                 class="rounded-lg border border-gray-700 bg-gray-900
                                                        p-2.5 font-mono text-xs text-white leading-tight">
                                                <div class="text-center font-bold tracking-[.15em] text-[11px]">MI NEGOCIO</div>
                                                <div class="text-center text-gray-400 text-[10px]">B12345678</div>
                                                <div class="border-t border-gray-700 my-1.5"></div>
                                                <div class="flex justify-between text-[10px]">
                                                    <span class="text-gray-300">Agua ×1</span><span>1,50 €</span>
                                                </div>
                                                <div class="flex justify-between text-[10px]">
                                                    <span class="text-gray-300">Pizza ×1</span><span>12,00 €</span>
                                                </div>
                                                <div class="border-t border-gray-700 my-1.5"></div>
                                                <div class="flex justify-between text-amber-400 font-bold text-[11px]">
                                                    <span>TOTAL</span><span>13,50 €</span>
                                                </div>
                                            </div>
                                        @else
                                            <div aria-hidden="true"
                                                 class="rounded-lg border border-gray-200 dark:border-gray-600
                                                        bg-white dark:bg-gray-900 p-2.5 font-mono text-xs
                                                        text-gray-700 dark:text-gray-300 leading-tight">
                                                <div class="text-[11px] font-medium">MI NEGOCIO</div>
                                                <div class="text-gray-500 dark:text-gray-500 text-[10px]">B12345678</div>
                                                <div class="mt-1.5 text-[10px]">Agua .......... 1,50</div>
                                                <div class="text-[10px]">Pizza ........ 12,00</div>
                                                <div class="text-[10px] text-gray-400">-------------------</div>
                                                <div class="font-bold text-[10px]">TOTAL ........ 13,50</div>
                                            </div>
                                        @endif

                                        {{-- Indicador "Seleccionada" visible cuando está activa --}}
                                        @if($isSelected)
                                            <div class="flex items-center gap-1.5 mt-auto pt-1">
                                                <svg class="h-3.5 w-3.5 {{ $accent['check'] }}"
                                                     aria-hidden="true" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span class="text-xs font-medium {{ $accent['check'] }}">
                                                    Seleccionada
                                                </span>
                                            </div>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>
                </section>

                {{-- ═══════════════════════════════════════════════════
                     SECCIÓN: PREVISUALIZACIÓN
                     ═══════════════════════════════════════════════════ --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden
                                ring-1 ring-gray-200 dark:ring-gray-700"
                         aria-labelledby="section-preview">

                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700
                                flex items-center gap-3">
                        <div class="flex items-center justify-center h-9 w-9 rounded-lg
                                    bg-teal-100 dark:bg-teal-900/40 shrink-0">
                            <svg class="h-5 w-5 text-teal-600 dark:text-teal-400"
                                 aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                         9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="section-preview"
                                class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Previsualización
                            </h3>
                            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                Muestra el ticket con los datos <strong class="font-medium text-gray-700
                                dark:text-gray-300">guardados actualmente</strong>.
                                Guarda los cambios primero para actualizar la vista.
                            </p>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:p-6 bg-gray-50 dark:bg-gray-900/50">
                        <iframe
                            src="{{ route('ticket-config.preview') }}"
                            title="Previsualización del ticket PDF con la configuración actual"
                            class="w-full rounded-lg border border-gray-200 dark:border-gray-700
                                   bg-white dark:bg-gray-900 shadow-sm"
                            style="height: 480px;"
                            loading="lazy"
                        ></iframe>
                    </div>
                </section>

                {{-- ─── ACCIONES ─── --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center
                            justify-end gap-3 pb-4">
                    <a
                        href="{{ route('ticket-config.preview') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 rounded-lg
                               border border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-700 px-4 py-2.5
                               text-sm font-medium text-gray-700 dark:text-gray-200
                               hover:bg-gray-50 dark:hover:bg-gray-600
                               focus:outline-none focus:ring-2 focus:ring-indigo-500
                               focus:ring-offset-2 dark:focus:ring-offset-gray-800
                               transition duration-150"
                        aria-label="Abrir previsualización del ticket en nueva pestaña"
                    >
                        <svg class="h-4 w-4 shrink-0" aria-hidden="true" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4
                                     M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Abrir en nueva pestaña</span>
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg
                               bg-indigo-600 px-5 py-2.5
                               text-sm font-semibold text-white shadow-sm
                               hover:bg-indigo-500 active:bg-indigo-700
                               focus:outline-none focus:ring-2 focus:ring-indigo-500
                               focus:ring-offset-2 dark:focus:ring-offset-gray-800
                               transition duration-150"
                    >
                        <svg class="h-4 w-4 shrink-0" aria-hidden="true" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Guardar configuración</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

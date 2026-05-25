{{-- @author SebastianBCF --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configuración del ticket PDF') }}
        </h2>
    </x-slot>

    <main id="main-content" class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash success --}}
            @if(session('success'))
                <div role="alert" class="rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 p-4 text-sm text-green-800 dark:text-green-300">
                    {{ session('success') }}
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

                {{-- ─── LOGO ─── --}}
                <section class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden" aria-labelledby="section-logo">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 id="section-logo" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Logo del negocio
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Se mostrará en la cabecera del ticket. Formatos: JPEG, PNG, WebP. Máximo 2&thinsp;MB.
                        </p>
                    </div>
                    <div class="px-4 py-5 sm:p-6 space-y-4">
                        @if($ticketConfig->hasLogo())
                            <div class="flex items-center gap-4">
                                <img
                                    src="{{ $ticketConfig->getLogoUrl() }}"
                                    alt="Logo actual del negocio"
                                    class="h-20 w-auto rounded border border-gray-200 dark:border-gray-600 object-contain bg-white p-1"
                                >
                                <p class="text-sm text-gray-500 dark:text-gray-400">Logo actual. Sube uno nuevo para reemplazarlo.</p>
                            </div>
                        @endif

                        <div>
                            <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $ticketConfig->hasLogo() ? 'Reemplazar logo' : 'Subir logo' }}
                            </label>
                            <input
                                type="file"
                                id="logo"
                                name="logo"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100
                                       file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                       file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700
                                       dark:file:bg-indigo-900/30 dark:file:text-indigo-300
                                       hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md"
                                aria-describedby="logo-hint @error('logo') logo-error @enderror"
                            >
                            <p id="logo-hint" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                JPEG, PNG, WebP · máximo 2 MB
                            </p>
                            @error('logo')
                                <p id="logo-error" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- ─── DATOS FISCALES ─── --}}
                <section class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden" aria-labelledby="section-fiscal">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 id="section-fiscal" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Datos fiscales
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            El nombre del negocio se toma automáticamente de tu perfil
                            ({{ Auth::user()->business_name ?? Auth::user()->name }}).
                        </p>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="max-w-sm">
                            <label for="tax_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                NIF / CIF del negocio
                            </label>
                            <input
                                type="text"
                                id="tax_id"
                                name="tax_id"
                                value="{{ old('tax_id', $ticketConfig->tax_id) }}"
                                maxlength="20"
                                placeholder="Ej: B12345678"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-gray-100 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                       @error('tax_id') border-red-500 @enderror"
                                aria-describedby="@error('tax_id') tax_id-error @enderror"
                            >
                            @error('tax_id')
                                <p id="tax_id-error" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- ─── PIE DEL TICKET ─── --}}
                <section class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden" aria-labelledby="section-footer">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 id="section-footer" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Pie del ticket
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Texto que aparece al final del ticket. Máximo 500 caracteres.
                        </p>
                    </div>
                    <div class="px-4 py-5 sm:p-6" x-data="{ count: {{ strlen(old('footer_text', $ticketConfig->footer_text ?? '')) }} }">
                        <label for="footer_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Texto del pie
                        </label>
                        <textarea
                            id="footer_text"
                            name="footer_text"
                            rows="3"
                            maxlength="500"
                            placeholder="Ej: Gracias por su visita. IVA incluido al 10%."
                            x-on:input="count = $event.target.value.length"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600
                                   dark:bg-gray-700 dark:text-gray-100 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                   @error('footer_text') border-red-500 @enderror"
                            aria-describedby="footer-counter @error('footer_text') footer_text-error @enderror"
                        >{{ old('footer_text', $ticketConfig->footer_text) }}</textarea>
                        <p id="footer-counter" class="mt-1 text-xs text-gray-400 dark:text-gray-500 text-right">
                            <span x-text="count"></span>/500 caracteres
                        </p>
                        @error('footer_text')
                            <p id="footer_text-error" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                {{-- ─── PLANTILLA ─── --}}
                <section class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden" aria-labelledby="section-template">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 id="section-template" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Plantilla del ticket
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Elige el estilo visual que se usará al generar el PDF.
                        </p>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <fieldset>
                            <legend class="sr-only">Selecciona una plantilla</legend>
                            @error('template')
                                <p role="alert" class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                @php
                                    $templateMeta = [
                                        'classic'  => ['label' => 'Clásica',      'desc' => 'Diseño tradicional con cabecera, línea divisora y tipografía serif.'],
                                        'modern'   => ['label' => 'Moderna',      'desc' => 'Diseño limpio con colores oscuros y tipografía sans-serif.'],
                                        'minimal'  => ['label' => 'Minimalista',  'desc' => 'Solo texto, sin decoraciones. Ideal para impresoras térmicas.'],
                                    ];
                                @endphp
                                @foreach($templates as $value)
                                    @php $meta = $templateMeta[$value]; $isSelected = old('template', $ticketConfig->template) === $value; @endphp
                                    <label
                                        for="template_{{ $value }}"
                                        class="relative flex flex-col cursor-pointer rounded-lg border-2 p-4 gap-2 transition
                                               {{ $isSelected
                                                    ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                                                    : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}"
                                    >
                                        <div class="flex items-center gap-3">
                                            <input
                                                type="radio"
                                                id="template_{{ $value }}"
                                                name="template"
                                                value="{{ $value }}"
                                                {{ $isSelected ? 'checked' : '' }}
                                                class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                                aria-required="true"
                                            >
                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $meta['label'] }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-snug">
                                            {{ $meta['desc'] }}
                                        </p>
                                        {{-- Maqueta ASCII de cada plantilla --}}
                                        @if($value === 'classic')
                                            <div class="mt-1 rounded border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 p-2 font-mono text-xs text-gray-700 dark:text-gray-300 leading-tight">
                                                <div class="text-center font-bold">MI NEGOCIO</div>
                                                <div class="text-center text-gray-400">NIF: B12345678</div>
                                                <div class="border-t my-1"></div>
                                                <div>Mesa 3 · 2 comensales</div>
                                                <div class="border-t my-1"></div>
                                                <div class="flex justify-between"><span>Agua</span><span>1,50 €</span></div>
                                                <div class="flex justify-between"><span>Pizza</span><span>12,00 €</span></div>
                                                <div class="border-t my-1"></div>
                                                <div class="flex justify-between font-bold"><span>TOTAL</span><span>13,50 €</span></div>
                                                <div class="text-center text-gray-400 text-xs mt-1">Gracias por su visita.</div>
                                            </div>
                                        @elseif($value === 'modern')
                                            <div class="mt-1 rounded border border-gray-800 dark:border-gray-400 bg-gray-900 p-2 font-mono text-xs text-white leading-tight">
                                                <div class="text-center font-bold tracking-widest">MI NEGOCIO</div>
                                                <div class="text-center text-gray-400 text-xs">B12345678</div>
                                                <div class="border-t border-gray-600 my-1"></div>
                                                <div class="flex justify-between"><span class="text-gray-300">Agua ×1</span><span>1,50 €</span></div>
                                                <div class="flex justify-between"><span class="text-gray-300">Pizza ×1</span><span>12,00 €</span></div>
                                                <div class="border-t border-gray-600 my-1"></div>
                                                <div class="flex justify-between text-yellow-400 font-bold"><span>TOTAL</span><span>13,50 €</span></div>
                                            </div>
                                        @else
                                            <div class="mt-1 rounded border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 p-2 font-mono text-xs text-gray-700 dark:text-gray-300 leading-tight">
                                                <div>MI NEGOCIO</div>
                                                <div class="text-gray-400">B12345678</div>
                                                <div>Agua .......... 1,50</div>
                                                <div>Pizza ........ 12,00</div>
                                                <div>-------------------</div>
                                                <div>TOTAL ........ 13,50</div>
                                            </div>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>
                </section>

                {{-- ─── PREVISUALIZACIÓN ─── --}}
                <section class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden" aria-labelledby="section-preview">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 id="section-preview" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Previsualización
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Muestra el ticket con los datos <strong>guardados actualmente</strong>.
                            Guarda los cambios primero para actualizar la previsualización.
                        </p>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <iframe
                            src="{{ route('ticket-config.preview') }}"
                            title="Previsualización del ticket PDF"
                            class="w-full rounded border border-gray-200 dark:border-gray-600 bg-white"
                            style="height: 480px;"
                            loading="lazy"
                        ></iframe>
                    </div>
                </section>

                {{-- ─── ACCIONES ─── --}}
                <div class="flex items-center justify-end gap-3 pb-4">
                    <a
                        href="{{ route('ticket-config.preview') }}"
                        target="_blank"
                        class="inline-flex items-center gap-1 rounded-md border border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200
                               hover:bg-gray-50 dark:hover:bg-gray-600
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        aria-label="Abrir previsualización en nueva pestaña"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Abrir en nueva pestaña
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold
                               text-white shadow-sm hover:bg-indigo-500
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                               transition duration-150"
                    >
                        Guardar configuración
                    </button>
                </div>
            </form>
        </div>
    </main>
</x-app-layout>

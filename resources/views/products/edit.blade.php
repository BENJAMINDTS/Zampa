<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-4 sm:p-6 mt-4 sm:mt-10
                bg-white dark:bg-gray-800 rounded-lg shadow-md">

        {{-- Cabecera --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('products.index') }}"
               aria-label="Volver al listado de productos"
               class="p-2.5 min-h-[44px] min-w-[44px] flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300
                      hover:bg-gray-100 dark:hover:bg-gray-700
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                      transition-colors">
                <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex items-center gap-2">
                <span class="p-1.5 bg-indigo-100 dark:bg-indigo-900/40 rounded-md" aria-hidden="true">
                    <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 leading-tight">
                    Editar producto
                    <span class="block text-sm font-normal text-gray-500 dark:text-gray-400 mt-0.5">{{ $product->name }}</span>
                </h2>
            </div>
        </div>

        {{-- Errores de validación --}}
        @if($errors->any())
        <div role="alert" aria-live="assertive"
             class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-3 mb-5 flex items-start gap-2">
            <svg class="h-4 w-4 text-red-500 shrink-0 mt-0.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <ul class="text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data"
              class="space-y-5"
              x-data="variantEditor({{ $product->variants->map(fn($v) => ['name' => $v->name, 'price' => $v->price, 'sort_order' => $v->sort_order])->values()->toJson() }})">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div>
                <label for="name" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <svg aria-hidden="true" class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h8m-8 6h16"/>
                    </svg>
                    Nombre del producto
                    <span aria-hidden="true" class="text-red-500">*</span>
                    <span class="sr-only">(obligatorio)</span>
                </label>
                <input type="text" name="name" id="name"
                       value="{{ old('name', $product->name) }}"
                       required aria-required="true"
                       aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                       aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
                       placeholder="Ej: Hamburguesa de ternera"
                       class="block w-full rounded-md border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                              placeholder-gray-400 dark:placeholder-gray-500
                              shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                              @error('name') border-red-500 @enderror">
                @error('name')
                <p id="name-error" role="alert" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Descripción --}}
            <div>
                <label for="description" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <svg aria-hidden="true" class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h10"/>
                    </svg>
                    Descripción
                    <span class="text-gray-400 dark:text-gray-500 font-normal">(opcional)</span>
                </label>
                <textarea name="description" id="description" rows="3"
                          placeholder="Ej: Hamburguesa de ternera con queso cheddar fundido…"
                          class="block w-full rounded-md border-gray-300 dark:border-gray-600
                                 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                 placeholder-gray-400 dark:placeholder-gray-500
                                 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $product->description) }}</textarea>
            </div>

            {{-- PRECIO / VARIANTES --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg aria-hidden="true" class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Precio / Variantes
                    </span>

                    <button type="button"
                            x-show="!useVariants"
                            @click="enableVariants"
                            class="inline-flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400
                                   hover:text-indigo-800 dark:hover:text-indigo-200 font-medium
                                   focus:outline-none focus:underline">
                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir variantes (ración, media…)
                    </button>
                    <button type="button"
                            x-show="useVariants"
                            @click="disableVariants"
                            class="inline-flex items-center gap-1 text-xs text-red-500 dark:text-red-400
                                   hover:text-red-700 dark:hover:text-red-300 font-medium
                                   focus:outline-none focus:underline">
                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Quitar variantes y usar precio directo
                    </button>
                </div>

                {{-- Precio directo --}}
                <div x-show="!useVariants">
                    <label for="price" class="sr-only">Precio (€)</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3
                                     text-gray-500 dark:text-gray-400 text-sm" aria-hidden="true">€</span>
                        <input type="number" step="0.01" name="price" id="price"
                               :value="useVariants ? '' : '{{ old('price', $product->price) }}'"
                               :required="!useVariants"
                               :disabled="useVariants"
                               aria-label="Precio en euros"
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                      shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-7"
                               placeholder="0.00" min="0">
                    </div>
                </div>

                {{-- Variantes --}}
                <div x-show="useVariants" class="space-y-2 mt-2">
                    <p class="flex items-start gap-2 text-xs text-amber-700 dark:text-amber-400
                               bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700
                               rounded px-3 py-2" role="note">
                        <svg aria-hidden="true" class="h-4 w-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Con variantes activas, el precio base se ignora. El precio viene de la variante elegida.
                    </p>

                    <template x-for="(variant, index) in variants" :key="index">
                        <div class="flex gap-2 items-end">
                            <div class="flex-1">
                                <label :for="'variant_name_' + index"
                                       class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-0.5">Nombre</label>
                                <input :id="'variant_name_' + index"
                                       :name="'variants[' + index + '][name]'"
                                       type="text"
                                       x-model="variant.name"
                                       placeholder="Ej: Ración entera"
                                       required
                                       class="block w-full rounded-md border-gray-300 dark:border-gray-600
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                              shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                       :aria-label="'Nombre de variante ' + (index + 1)">
                            </div>
                            <div class="w-28">
                                <label :for="'variant_price_' + index"
                                       class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-0.5">Precio (€)</label>
                                <input :id="'variant_price_' + index"
                                       :name="'variants[' + index + '][price]'"
                                       type="number" step="0.01" min="0"
                                       x-model="variant.price"
                                       placeholder="0.00"
                                       required
                                       class="block w-full rounded-md border-gray-300 dark:border-gray-600
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                              shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                       :aria-label="'Precio de variante ' + (index + 1)">
                            </div>
                            <input type="hidden" :name="'variants[' + index + '][sort_order]'" :value="index">
                            <button type="button"
                                    @click="removeVariant(index)"
                                    class="mb-0.5 flex-shrink-0 p-1 rounded text-red-500 hover:text-red-700 dark:hover:text-red-400
                                           hover:bg-red-50 dark:hover:bg-red-900/20
                                           focus:outline-none focus:ring-2 focus:ring-red-400"
                                    :aria-label="'Eliminar variante ' + (variant.name || (index + 1))">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                    <button type="button"
                            @click="addVariant"
                            class="inline-flex items-center gap-1 mt-1 text-sm text-indigo-600 dark:text-indigo-400
                                   hover:text-indigo-800 dark:hover:text-indigo-200 font-medium
                                   focus:outline-none focus:underline">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir otra variante
                    </button>
                </div>
            </div>

            {{-- Categorías --}}
            <div>
                <label for="category_ids" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <svg aria-hidden="true" class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Categorías
                    <span aria-hidden="true" class="text-red-500">*</span>
                    <span class="sr-only">(obligatorio)</span>
                </label>
                @php $selectedCatIds = old('category_ids', $product->categories->pluck('id')->toArray()); @endphp
                <select name="category_ids[]" id="category_ids" multiple required aria-required="true"
                        aria-multiselectable="true"
                        aria-invalid="{{ $errors->has('category_ids') ? 'true' : 'false' }}"
                        size="{{ min($categories->count(), 6) }}"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                               shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                               @error('category_ids') border-red-500 @enderror">
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ in_array($category->id, $selectedCatIds) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Mantén Ctrl para seleccionar varias categorías</p>
                @error('category_ids')
                <p id="category-error" role="alert" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Foto --}}
            <div x-data="{ preview: null, fileName: null }">
                <label for="image" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <svg aria-hidden="true" class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Foto del producto
                    <span class="text-gray-400 dark:text-gray-500 font-normal">(máx. 2 MB)</span>
                </label>

                {{-- Imagen actual --}}
                @if($product->image)
                <div x-show="!preview" class="my-2 flex items-center gap-3">
                    <img src="{{ asset('storage/' . $product->image) }}"
                         alt="Imagen actual de {{ $product->name }}"
                         class="h-16 w-16 sm:h-20 sm:w-20 object-cover rounded-md border border-gray-200 dark:border-gray-600 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Imagen actual.<br>Sube una nueva para reemplazarla.
                    </p>
                </div>
                @endif

                {{-- Preview nueva imagen --}}
                <div x-show="preview" x-cloak class="mt-2 flex items-center gap-3">
                    <img :src="preview" alt="Vista previa de la nueva foto"
                         class="h-16 w-16 sm:h-20 sm:w-20 object-cover rounded-md border border-indigo-200 dark:border-indigo-700 shadow-sm ring-2 ring-indigo-400 ring-offset-1">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        <p class="flex items-center gap-1 font-medium text-gray-700 dark:text-gray-200">
                            <svg aria-hidden="true" class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="fileName" class="truncate max-w-[160px]"></span>
                        </p>
                        <button type="button"
                                @click="preview = null; fileName = null; $refs.imageInput.value = ''"
                                class="mt-1 inline-flex items-center gap-1 text-red-500 hover:text-red-700
                                       focus:outline-none focus:underline">
                            <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Quitar imagen nueva
                        </button>
                    </div>
                </div>

                <input type="file"
                       name="image" id="image"
                       x-ref="imageInput"
                       accept="image/jpeg,image/png,image/jpg,image/webp"
                       @change="
                           const f = $event.target.files[0];
                           if (f) { preview = URL.createObjectURL(f); fileName = f.name; }
                           else   { preview = null; fileName = null; }
                       "
                       class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400
                              file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100
                              dark:file:bg-indigo-900/30 dark:file:text-indigo-400
                              focus:outline-none">
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">JPEG, PNG, WebP — máximo 2 MB</p>
            </div>

            {{-- Botón guardar --}}
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2
                           py-2.5 px-4 rounded-md shadow-sm text-sm font-semibold
                           bg-indigo-600 text-white hover:bg-indigo-700
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                           disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar cambios
            </button>
        </form>

        {{-- Alérgenos detectados --}}
        @if($product->allergens->isNotEmpty())
        <div class="mt-5 pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                <svg aria-hidden="true" class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Alérgenos detectados
            </p>
            <div class="flex flex-wrap gap-2" role="list" aria-label="Alérgenos de {{ $product->name }}">
                @foreach($product->allergens->flatMap(fn($i) => $i->allergen_types ?? [])->unique()->values() as $slug)
                    <x-allergen-badge :slug="$slug" />
                @endforeach
            </div>
        </div>
        @endif

        {{-- Configurar ingredientes --}}
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('products.ingredients.edit', $product) }}"
               aria-label="Configurar ingredientes y alérgenos del producto {{ $product->name }}"
               class="w-full inline-flex items-center justify-center gap-2
                      py-2.5 px-4 rounded-md shadow-sm text-sm font-semibold
                      bg-amber-500 text-white hover:bg-amber-600
                      focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2
                      transition-colors">
                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Configurar ingredientes y alérgenos
            </a>
        </div>
    </div>

    @push('scripts')
    <script>
    function variantEditor(initialVariants) {
        return {
            useVariants: initialVariants.length > 0,
            variants: initialVariants.length > 0 ? initialVariants : [],

            enableVariants() {
                this.useVariants = true;
                if (this.variants.length === 0) {
                    this.variants.push({ name: '', price: '', sort_order: 0 });
                }
            },

            disableVariants() {
                this.useVariants = false;
            },

            addVariant() {
                this.variants.push({ name: '', price: '', sort_order: this.variants.length });
            },

            removeVariant(index) {
                this.variants.splice(index, 1);
                if (this.variants.length === 0) {
                    this.useVariants = false;
                }
            },
        };
    }
    </script>
    @endpush
</x-app-layout>

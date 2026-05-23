<x-app-layout>
  <div class="max-w-2xl mx-auto px-4 sm:px-6 py-4 sm:p-6 bg-white rounded-lg shadow-md mt-4 sm:mt-10">
    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-800">Añadir Nuevo Plato</h2>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data"
          class="space-y-3 sm:space-y-4"
          x-data="variantEditor([])">
      @csrf

      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nombre del plato</label>
        <input type="text" name="name" id="name" required aria-required="true"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Descripción (Opcional)</label>
        <textarea name="description" id="description" rows="3"
                  placeholder="Ej: Hamburguesa de ternera con queso cheddar fundido..."
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
      </div>

      {{-- SECCIÓN PRECIO / VARIANTES --}}
      <div>
          <div class="flex items-center justify-between mb-1">
              <span class="block text-sm font-medium text-gray-700">Precio / Variantes</span>
              <button type="button"
                      x-show="!useVariants"
                      @click="enableVariants"
                      class="text-xs text-indigo-600 hover:text-indigo-800 font-medium focus:outline-none focus:underline">
                  + Añadir variantes (ración, media ración…)
              </button>
              <button type="button"
                      x-show="useVariants"
                      @click="disableVariants"
                      class="text-xs text-red-500 hover:text-red-700 font-medium focus:outline-none focus:underline">
                  ✕ Quitar variantes y usar precio directo
              </button>
          </div>

          {{-- Precio directo --}}
          <div x-show="!useVariants">
              <label for="price" class="sr-only">Precio (€)</label>
              <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 text-sm">€</span>
                  <input type="number" step="0.01" name="price" id="price"
                         :required="!useVariants"
                         :disabled="useVariants"
                         class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-7"
                         placeholder="0.00" min="0">
              </div>
          </div>

          {{-- Sección variantes --}}
          <div x-show="useVariants" class="space-y-2 mt-2">
              <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded px-3 py-2" role="note">
                  Con variantes activas, el precio base del producto se ignora. El precio viene de la variante elegida.
              </p>

              <template x-for="(variant, index) in variants" :key="index">
                  <div class="flex gap-2 items-end">
                      <div class="flex-1">
                          <label :for="'variant_name_' + index" class="block text-xs font-medium text-gray-600 mb-0.5">Nombre</label>
                          <input :id="'variant_name_' + index"
                                 :name="'variants[' + index + '][name]'"
                                 type="text"
                                 x-model="variant.name"
                                 placeholder="Ej: Ración entera"
                                 required
                                 class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                 :aria-label="'Nombre de variante ' + (index + 1)">
                      </div>
                      <div class="w-28">
                          <label :for="'variant_price_' + index" class="block text-xs font-medium text-gray-600 mb-0.5">Precio (€)</label>
                          <input :id="'variant_price_' + index"
                                 :name="'variants[' + index + '][price]'"
                                 type="number"
                                 step="0.01"
                                 min="0"
                                 x-model="variant.price"
                                 placeholder="0.00"
                                 required
                                 class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                 :aria-label="'Precio de variante ' + (index + 1)">
                      </div>
                      <input type="hidden" :name="'variants[' + index + '][sort_order]'" :value="index">
                      <button type="button"
                              @click="removeVariant(index)"
                              class="mb-0.5 flex-shrink-0 text-red-500 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 rounded"
                              :aria-label="'Eliminar variante ' + (variant.name || (index + 1))">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                          </svg>
                      </button>
                  </div>
              </template>

              <button type="button"
                      @click="addVariant"
                      class="mt-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium focus:outline-none focus:underline">
                  + Añadir otra variante
              </button>
          </div>
      </div>

      <div>
        <label for="category_id" class="block text-sm font-medium text-gray-700">Categoría</label>
        <select name="category_id" id="category_id" required aria-required="true"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          <option value="" disabled selected>Selecciona una categoría...</option>
          @foreach($categories as $category)
          <option value="{{ $category->id }}">{{ $category->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label for="image" class="block text-sm font-medium text-gray-700">Foto del plato (Max: 2MB)</label>
        <input type="file" name="image" id="image" accept="image/jpeg, image/png, image/webp"
               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
      </div>

      <div class="flex flex-col sm:flex-row gap-3 pt-1">
        <button type="submit" name="configure_ingredients" value="1"
                class="flex-1 py-2 px-4 bg-orange-500 text-white rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 font-semibold">
          Guardar y configurar ingredientes
        </button>
        <button type="submit"
                class="flex-1 py-2 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Guardar Plato
        </button>
      </div>
    </form>
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

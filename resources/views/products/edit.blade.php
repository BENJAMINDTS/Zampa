<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-4 sm:p-6 bg-white rounded-lg shadow-md mt-4 sm:mt-10">
        <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-800">Editar Plato: {{ $product->name }}</h2>

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-3 sm:space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre del plato</label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $product->description) }}</textarea>
            </div>
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Precio (€)</label>
                <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $product->price) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Categoría</label>
                <select name="category_id" id="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">Foto del plato (Max: 2MB)</label>
                @if($product->image)
                <div class="my-2">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="Imagen actual de {{ $product->name }}" class="h-16 w-16 sm:h-20 sm:w-20 object-cover rounded-md border">
                    <p class="text-xs text-gray-500 mt-1">Imagen actual. Sube una nueva si deseas reemplazarla.</p>
                </div>
                @endif
                <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500">
            </div>

            <button type="submit" class="w-full py-2 px-4 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                Actualizar Plato
            </button>
        </form>

        @if($product->allergens->isNotEmpty())
        <div class="mt-4 pt-4 border-t border-gray-200">
            <p class="text-sm font-medium text-gray-700 mb-2">Alérgenos detectados:</p>
            <div class="flex flex-wrap gap-1" role="list" aria-label="Alérgenos de {{ $product->name }}">
                @foreach($product->allergens as $allergen)
                    <span role="listitem"
                          class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded-full border border-red-200">
                        <span aria-hidden="true">⚠️</span>{{ $allergen->name }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mt-4 pt-4 border-t dark:border-gray-700">
            <a href="{{ route('products.ingredients.edit', $product) }}"
               class="w-full flex justify-center py-2 px-4 border border-orange-500
                      rounded-md shadow-sm text-sm font-medium
                      text-orange-600 dark:text-orange-400
                      hover:bg-orange-50 dark:hover:bg-orange-900/20
                      focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2
                      transition-colors"
               aria-label="Configurar ingredientes y alérgenos del plato {{ $product->name }}">
                ⚙️ Configurar ingredientes y alérgenos
            </a>
        </div>
    </div>
</x-app-layout>
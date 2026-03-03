<x-app-layout>
    <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Editar Plato: {{ $product->name }}</h2>

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre del plato</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $product->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Precio (€)</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Categoría</label>
                <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Foto del plato (Max: 2MB)</label>
                @if($product->image)
                <div class="my-2">
                    <img src="{{ asset('storage/' . $product->image) }}" class="h-20 w-20 object-cover rounded-md border">
                    <p class="text-xs text-gray-500 mt-1">Imagen actual. Sube una nueva si deseas reemplazarla.</p>
                </div>
                @endif
                <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500">
            </div>

            <button type="submit" class="w-full py-2 px-4 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                Actualizar Plato
            </button>
        </form>
    </div>
</x-app-layout>
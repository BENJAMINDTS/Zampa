<x-app-layout>
  <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Añadir Nuevo Plato</h2>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nombre del plato</label>
        <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Descripción (Opcional)</label>
        <textarea name="description" id="description" rows="3" placeholder="Ej: Hamburguesa de ternera con queso cheddar fundido..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
      </div>
      <div>
        <label for="price" class="block text-sm font-medium text-gray-700">Precio (€)</label>
        <input type="number" step="0.01" name="price" id="price" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <div>
        <label for="category_id" class="block text-sm font-medium text-gray-700">Categoría</label>
        <select name="category_id" id="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          <option value="" disabled selected>Selecciona una categoría...</option>
          @foreach($categories as $category)
          <option value="{{ $category->id }}">{{ $category->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label for="image" class="block text-sm font-medium text-gray-700">Foto del plato (Max: 2MB)</label>
        <input type="file" name="image" id="image" accept="image/jpeg, image/png, image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
      </div>

      <button type="submit" class="w-full py-2 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Guardar Plato
      </button>
    </form>
  </div>
</x-app-layout>
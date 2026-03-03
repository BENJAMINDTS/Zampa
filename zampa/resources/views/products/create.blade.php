<x-app-layout>
  <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Añadir Nuevo Plato</h2>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-medium text-gray-700">Nombre del plato</label>
        <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Precio (€)</label>
        <input type="number" step="0.01" name="price" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Categoría ID</label>
        <input type="number" name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Foto del plato (Max: 2MB)</label>
        <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500">
      </div>

      <button type="submit" class="w-full py-2 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
        Guardar Plato
      </button>
    </form>
  </div>
</x-app-layout>
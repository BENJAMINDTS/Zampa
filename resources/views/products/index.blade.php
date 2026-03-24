<x-app-layout>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 mt-4 sm:mt-10">
    <div class="flex justify-between items-center mb-4 sm:mb-6">
      <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Mi Carta Digital</h2>
      <a href="{{ route('products.create') }}" class="bg-green-600 text-white px-3 py-2 sm:px-4 rounded-md hover:bg-green-700 text-sm sm:text-base">
        + Añadir Plato
      </a>
    </div>

    @if(session('success'))
    <div role="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
      {{ session('success') }}
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Imagen</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
            <th class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @foreach($products as $product)
          <tr>
            <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
              @if($product->image)
              <img src="{{ asset('storage/' . $product->image) }}" alt="Foto de {{ $product->name }}" class="h-10 w-10 sm:h-12 sm:w-12 object-cover rounded-md">
              @else
              <span class="text-gray-400 text-sm">Sin imagen</span>
              @endif
            </td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap font-medium text-gray-900 text-sm sm:text-base">{{ $product->name }}</td>
            <td class="hidden md:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-gray-500">{{ number_format($product->price, 2) }} €</td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-100 px-3 py-1 rounded text-center">Editar</a>
                <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres borrar este plato?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" aria-label="Borrar plato {{ $product->name }}" class="w-full text-red-600 hover:text-red-900 bg-red-100 px-3 py-1 rounded">Borrar</button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</x-app-layout>
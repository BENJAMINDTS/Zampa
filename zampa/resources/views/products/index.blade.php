<x-app-layout>
  <div class="max-w-6xl mx-auto p-6 mt-10">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Mi Carta Digital</h2>
      <a href="{{ route('products.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
        + Añadir Plato
      </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
      {{ session('success') }}
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Imagen</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @foreach($products as $product)
          <tr>
            <td class="px-6 py-4 whitespace-nowrap">
              @if($product->image)
              <img src="{{ asset('storage/' . $product->image) }}" alt="Foto de {{ $product->name }}" class="h-12 w-12 object-cover rounded-md">
              @else
              <span class="text-gray-400 text-sm">Sin imagen</span>
              @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $product->name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ number_format($product->price, 2) }} €</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</x-app-layout>
{{-- @author BenjaminDTS --}}
{{-- @author SebastianBCF --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Mi equipo') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">

        @if(session('success'))
          <div role="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
          </div>
        @endif

        <div class="flex justify-end mb-4">
          <a href="{{ route('staff.create') }}"
             class="bg-orange-500 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 text-white font-bold py-2 px-4 rounded">
            + Añadir persona
          </a>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg shadow"
                 aria-label="Lista de personal">
            <thead>
              <tr class="bg-gray-100 dark:bg-gray-700 text-left text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase">
                <th class="px-4 py-3">Nombre</th>
                <th class="hidden sm:table-cell px-4 py-3">Email</th>
                <th class="px-4 py-3">Rol</th>
                <th class="hidden sm:table-cell px-4 py-3">Alta</th>
                <th class="px-4 py-3 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
              @forelse($staff as $member)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                  <td class="px-4 py-3 font-medium">{{ $member->name }}</td>
                  <td class="hidden sm:table-cell px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                    {{ $member->email }}
                  </td>
                  <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs font-semibold
                      {{ $member->role === 'waiter' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                      {{ $member->role === 'waiter' ? 'Camarero' : 'Cocinero' }}
                    </span>
                  </td>
                  <td class="hidden sm:table-cell px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ $member->created_at->format('d/m/Y') }}
                  </td>
                  <td class="px-4 py-3 text-right">
                    <form action="{{ route('staff.destroy', $member) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar a {{ $member->name }} del equipo?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                              aria-label="Eliminar a {{ $member->name }}"
                              class="text-sm bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 py-1 px-3 rounded focus:outline-none focus:ring-2 focus:ring-red-400">
                        Eliminar
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                    Aún no has añadido personal.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($staff->hasPages())
          <nav aria-label="Paginación del personal" class="mt-6">
            {{ $staff->links() }}
          </nav>
        @endif

      </div>
    </div>
  </div>
</x-app-layout>

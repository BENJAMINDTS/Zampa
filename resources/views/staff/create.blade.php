{{-- @author BenjaminDTS --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Añadir Personal') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">

        <form method="POST" action="{{ route('staff.store') }}">
          @csrf

          {{-- Nombre --}}
          <div class="mb-4">
            <label for="name" class="block text-gray-700 dark:text-gray-300 mb-2">
              Nombre <span aria-hidden="true">*</span>
            </label>
            <input type="text" name="name" id="name" required aria-required="true"
                   value="{{ old('name') }}"
                   class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white @error('name') border-red-500 @enderror">
            @error('name')
              <p id="error-name" role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Email --}}
          <div class="mb-4">
            <label for="email" class="block text-gray-700 dark:text-gray-300 mb-2">
              Correo electrónico <span aria-hidden="true">*</span>
            </label>
            <input type="email" name="email" id="email" required aria-required="true"
                   value="{{ old('email') }}"
                   class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white @error('email') border-red-500 @enderror">
            @error('email')
              <p id="error-email" role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Contraseña --}}
          <div class="mb-4">
            <label for="password" class="block text-gray-700 dark:text-gray-300 mb-2">
              Contraseña <span aria-hidden="true">*</span>
            </label>
            <input type="password" name="password" id="password" required aria-required="true" minlength="8"
                   class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white @error('password') border-red-500 @enderror">
            @error('password')
              <p id="error-password" role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Confirmar contraseña --}}
          <div class="mb-4">
            <label for="password_confirmation" class="block text-gray-700 dark:text-gray-300 mb-2">
              Confirmar contraseña <span aria-hidden="true">*</span>
            </label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   required aria-required="true" minlength="8"
                   class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
          </div>

          {{-- Rol --}}
          <div class="mb-6">
            <label for="role" class="block text-gray-700 dark:text-gray-300 mb-2">
              Rol <span aria-hidden="true">*</span>
            </label>
            <select name="role" id="role" required aria-required="true"
                    class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white @error('role') border-red-500 @enderror">
              <option value="">— Selecciona un rol —</option>
              <option value="waiter" {{ old('role') === 'waiter' ? 'selected' : '' }}>Camarero</option>
              <option value="kitchen" {{ old('role') === 'kitchen' ? 'selected' : '' }}>Cocinero</option>
            </select>
            @error('role')
              <p id="error-role" role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="flex items-center gap-4">
            <button type="submit"
                    class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
              Dar de alta
            </button>
            <a href="{{ route('staff.index') }}"
               class="text-gray-600 dark:text-gray-400 hover:underline">
              Cancelar
            </a>
          </div>

        </form>

      </div>
    </div>
  </div>
</x-app-layout>

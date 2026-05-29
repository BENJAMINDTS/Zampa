<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        @if($errors->any())
        <div role="alert" aria-live="assertive"
             class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-3 mb-4 flex items-start gap-2">
            <svg class="h-4 w-4 text-red-500 shrink-0 mt-0.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <ul class="text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" :required="true" />
            <x-text-input
                id="name"
                class="block mt-1 w-full @error('name') border-red-500 @enderror"
                type="text"
                name="name"
                :value="old('name')"
                aria-required="true"
                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
                autofocus
                autocomplete="name"
            />
            <x-input-error :messages="$errors->get('name')" id="name-error" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" :required="true" />
            <x-text-input
                id="email"
                class="block mt-1 w-full @error('email') border-red-500 @enderror"
                type="email"
                name="email"
                :value="old('email')"
                aria-required="true"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" id="email-error" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" :required="true" />
            <x-text-input
                id="password"
                class="block mt-1 w-full @error('password') border-red-500 @enderror"
                type="password"
                name="password"
                aria-required="true"
                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" id="password-error" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" :required="true" />
            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full @error('password_confirmation') border-red-500 @enderror"
                type="password"
                name="password_confirmation"
                aria-required="true"
                aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
                aria-describedby="{{ $errors->has('password_confirmation') ? 'password-confirmation-error' : '' }}"
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" id="password-confirmation-error" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4 gap-3">
            <a class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
               href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button>
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

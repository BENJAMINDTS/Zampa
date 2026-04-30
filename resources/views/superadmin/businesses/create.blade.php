@extends('layouts.superadmin')

@section('header', 'Nuevo negocio')

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('superadmin.businesses.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver a negocios
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-white mt-2">Nuevo negocio</h1>
        <p class="text-slate-400 text-sm mt-1">Crea una cuenta de gerente y asígnale un plan.</p>
    </div>

    <section class="bg-slate-900 rounded-xl ring-1 ring-slate-800 p-6">

        <form method="POST" action="{{ route('superadmin.businesses.store') }}" novalidate>
            @csrf

            <div class="space-y-5">

                {{-- Sección: datos del negocio --}}
                <div class="pb-4 border-b border-slate-800">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Datos del negocio</h2>
                </div>

                {{-- Nombre del negocio --}}
                <div>
                    <label for="business_name" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nombre del negocio <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="business_name"
                           name="business_name"
                           type="text"
                           value="{{ old('business_name') }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('business_name') ? 'error-business_name' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('business_name') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="Ej: Restaurante El Rincón">
                    @error('business_name')
                        <p id="error-business_name" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dirección --}}
                <div>
                    <label for="address" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Dirección <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="address"
                           name="address"
                           type="text"
                           value="{{ old('address') }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('address') ? 'error-address' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('address') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="Ej: Calle Mayor 12, Madrid">
                    @error('address')
                        <p id="error-address" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Lat / Lng --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="lat" class="block text-sm font-medium text-slate-300 mb-1.5">Latitud</label>
                        <input id="lat"
                               name="lat"
                               type="number"
                               step="0.0000001"
                               value="{{ old('lat') }}"
                               aria-describedby="{{ $errors->has('lat') ? 'error-lat' : '' }}"
                               class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                      px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                      {{ $errors->has('lat') ? 'border-red-500 focus:ring-red-500' : '' }}"
                               placeholder="40.4168">
                        @error('lat')
                            <p id="error-lat" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="lng" class="block text-sm font-medium text-slate-300 mb-1.5">Longitud</label>
                        <input id="lng"
                               name="lng"
                               type="number"
                               step="0.0000001"
                               value="{{ old('lng') }}"
                               aria-describedby="{{ $errors->has('lng') ? 'error-lng' : '' }}"
                               class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                      px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                      {{ $errors->has('lng') ? 'border-red-500 focus:ring-red-500' : '' }}"
                               placeholder="-3.7038">
                        @error('lng')
                            <p id="error-lng" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Plan --}}
                <div>
                    <label for="plan_id" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Plan de suscripción <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <select id="plan_id"
                            name="plan_id"
                            aria-required="true"
                            aria-describedby="{{ $errors->has('plan_id') ? 'error-plan_id' : '' }}"
                            class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white
                                   px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                   {{ $errors->has('plan_id') ? 'border-red-500 focus:ring-red-500' : '' }}">
                        <option value="">-- Selecciona un plan --</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} — {{ number_format($plan->price, 2, ',', '.') }} €/mes · {{ $plan->max_tables }} mesas
                            </option>
                        @endforeach
                    </select>
                    @error('plan_id')
                        <p id="error-plan_id" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sección: datos del responsable --}}
                <div class="pt-4 pb-4 border-b border-slate-800">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Datos del responsable</h2>
                </div>

                {{-- Nombre del responsable --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nombre completo <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="name"
                           name="name"
                           type="text"
                           value="{{ old('name') }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('name') ? 'error-name' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="Ej: Juan García">
                    @error('name')
                        <p id="error-name" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Email <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('email') ? 'error-email' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('email') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="gerente@restaurante.com">
                    @error('email')
                        <p id="error-email" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Contraseña <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="password"
                           name="password"
                           type="password"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('password') ? 'error-password' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('password') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="Mínimo 8 caracteres">
                    @error('password')
                        <p id="error-password" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirmar contraseña --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Confirmar contraseña <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="password_confirmation"
                           name="password_confirmation"
                           type="password"
                           aria-required="true"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
                           placeholder="Repite la contraseña">
                </div>

            </div>

            <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-slate-800">
                <a href="{{ route('superadmin.businesses.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-600">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2 rounded-lg bg-amber-400 text-slate-900 text-sm font-semibold hover:bg-amber-300 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                    Crear negocio
                </button>
            </div>

        </form>

    </section>

</div>

@endsection

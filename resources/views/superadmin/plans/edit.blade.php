@extends('layouts.superadmin')

@section('header', 'Editar plan')

@section('content')

<div class="max-w-xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('superadmin.plans.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver a planes
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-white mt-2">Editar plan: {{ $plan->name }}</h1>
    </div>

    <section class="bg-slate-900 rounded-xl ring-1 ring-slate-800 p-6">

        <form method="POST" action="{{ route('superadmin.plans.update', $plan) }}" novalidate>
            @csrf
            @method('PUT')

            <div class="space-y-5">

                {{-- Nombre --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nombre del plan <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="name"
                           name="name"
                           type="text"
                           value="{{ old('name', $plan->name) }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('name') ? 'error-name' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="Ej: Plan Básico">
                    @error('name')
                        <p id="error-name" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Precio mensual --}}
                <div>
                    <label for="price_monthly" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Precio mensual (€) <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="price_monthly"
                           name="price_monthly"
                           type="number"
                           min="0"
                           step="0.01"
                           value="{{ old('price_monthly', $plan->price_monthly ?? $plan->price) }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('price_monthly') ? 'error-price_monthly' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('price_monthly') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="29.99">
                    @error('price_monthly')
                        <p id="error-price_monthly" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Precio anual --}}
                <div>
                    <label for="price_yearly" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Precio anual (€)
                        <span class="text-slate-500 text-xs">(opcional)</span>
                    </label>
                    <input id="price_yearly"
                           name="price_yearly"
                           type="number"
                           min="0"
                           step="0.01"
                           value="{{ old('price_yearly', $plan->price_yearly) }}"
                           aria-describedby="{{ $errors->has('price_yearly') ? 'error-price_yearly' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('price_yearly') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="299.90">
                    @error('price_yearly')
                        <p id="error-price_yearly" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Límites --}}
                <p class="text-xs text-slate-500">
                    Deja en blanco los límites para plan ilimitado (Premium).
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    {{-- Límite de mesas --}}
                    <div>
                        <label for="max_tables" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Máx. mesas
                        </label>
                        <input id="max_tables"
                               name="max_tables"
                               type="number"
                               min="1"
                               value="{{ old('max_tables', $plan->max_tables) }}"
                               aria-describedby="{{ $errors->has('max_tables') ? 'error-max_tables' : '' }}"
                               class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                      px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                      {{ $errors->has('max_tables') ? 'border-red-500 focus:ring-red-500' : '' }}"
                               placeholder="∞">
                        @error('max_tables')
                            <p id="error-max_tables" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Límite de personal --}}
                    <div>
                        <label for="max_staff" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Máx. personal
                        </label>
                        <input id="max_staff"
                               name="max_staff"
                               type="number"
                               min="1"
                               value="{{ old('max_staff', $plan->max_staff) }}"
                               aria-describedby="{{ $errors->has('max_staff') ? 'error-max_staff' : '' }}"
                               class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                      px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                      {{ $errors->has('max_staff') ? 'border-red-500 focus:ring-red-500' : '' }}"
                               placeholder="∞">
                        @error('max_staff')
                            <p id="error-max_staff" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Límite de plantas --}}
                    <div>
                        <label for="max_floors" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Máx. plantas
                        </label>
                        <input id="max_floors"
                               name="max_floors"
                               type="number"
                               min="1"
                               value="{{ old('max_floors', $plan->max_floors) }}"
                               aria-describedby="{{ $errors->has('max_floors') ? 'error-max_floors' : '' }}"
                               class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                      px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                      {{ $errors->has('max_floors') ? 'border-red-500 focus:ring-red-500' : '' }}"
                               placeholder="∞">
                        @error('max_floors')
                            <p id="error-max_floors" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>

            <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-slate-800">
                <a href="{{ route('superadmin.plans.index') }}"
                   class="inline-flex items-center px-4 py-2.5 min-h-[44px] rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-600">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 min-h-[44px] rounded-lg bg-amber-400 text-slate-900 text-sm font-semibold hover:bg-amber-300 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                    Guardar cambios
                </button>
            </div>

        </form>

    </section>

</div>

@endsection

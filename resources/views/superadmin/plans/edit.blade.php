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

                {{-- Precio --}}
                <div>
                    <label for="price" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Precio mensual (€) <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="price"
                           name="price"
                           type="number"
                           min="0"
                           step="0.01"
                           value="{{ old('price', $plan->price) }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('price') ? 'error-price' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('price') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="0.00">
                    @error('price')
                        <p id="error-price" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Límite de mesas --}}
                <div>
                    <label for="max_tables" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Límite de mesas <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="max_tables"
                           name="max_tables"
                           type="number"
                           min="1"
                           max="500"
                           value="{{ old('max_tables', $plan->max_tables) }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('max_tables') ? 'error-max_tables' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('max_tables') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="Ej: 10">
                    @error('max_tables')
                        <p id="error-max_tables" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
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

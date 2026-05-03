@extends('layouts.superadmin')

@section('header', 'Panel principal')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    {{-- Bienvenida --}}
    <section aria-labelledby="bienvenida-title">
        <h1 id="bienvenida-title" class="text-2xl font-bold text-white mb-1">
            Bienvenido, {{ Auth::user()->name }}
        </h1>
        <p class="text-slate-400 text-sm">Panel de superadministración de Zampa</p>
    </section>

    {{-- Cards de acceso rápido --}}
    <section aria-labelledby="accesos-title">
        <h2 id="accesos-title" class="text-sm font-semibold text-slate-400 uppercase tracking-widest mb-4">
            Accesos rápidos
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- Planes --}}
            <a href="{{ route('superadmin.plans.index') }}"
               class="group flex items-center gap-4 p-5 rounded-xl bg-slate-900 border border-slate-800
                      hover:border-amber-400/50 hover:bg-slate-800 transition-all">
                <span class="w-12 h-12 rounded-lg bg-amber-400/10 flex items-center justify-center flex-shrink-0
                             group-hover:bg-amber-400/20 transition-colors">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
                <div>
                    <p class="text-white font-semibold">Planes</p>
                    <p class="text-slate-400 text-sm">Gestión de planes SaaS</p>
                </div>
            </a>

            {{-- Negocios --}}
            <a href="{{ route('superadmin.businesses.index') }}"
               class="group flex items-center gap-4 p-5 rounded-xl bg-slate-900 border border-slate-800
                      hover:border-amber-400/50 hover:bg-slate-800 transition-all">
                <span class="w-12 h-12 rounded-lg bg-amber-400/10 flex items-center justify-center flex-shrink-0
                             group-hover:bg-amber-400/20 transition-colors">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <div>
                    <p class="text-white font-semibold">Negocios</p>
                    <p class="text-slate-400 text-sm">Administrar restaurantes</p>
                </div>
            </a>

            {{-- Mapa --}}
            <a href="{{ route('superadmin.map') }}"
               class="group flex items-center gap-4 p-5 rounded-xl bg-slate-900 border border-slate-800
                      hover:border-amber-400/50 hover:bg-slate-800 transition-all">
                <span class="w-12 h-12 rounded-lg bg-amber-400/10 flex items-center justify-center flex-shrink-0
                             group-hover:bg-amber-400/20 transition-colors">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </span>
                <div>
                    <p class="text-white font-semibold">Mapa</p>
                    <p class="text-slate-400 text-sm">Distribución geográfica</p>
                </div>
            </a>

        </div>
    </section>

    {{-- Placeholder estadísticas (B13.3 y B13.4 las poblan con datos reales) --}}
    <section aria-labelledby="stats-title">
        <h2 id="stats-title" class="text-sm font-semibold text-slate-400 uppercase tracking-widest mb-4">
            Estadísticas globales
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800">
                <p class="text-slate-400 text-xs uppercase tracking-widest mb-1">Negocios registrados</p>
                <p class="text-3xl font-bold text-white">{{ $totalBusinesses }}</p>
            </div>
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800">
                <p class="text-slate-400 text-xs uppercase tracking-widest mb-1">Planes disponibles</p>
                <p class="text-3xl font-bold text-white">{{ $totalPlans }}</p>
            </div>
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800">
                <p class="text-slate-400 text-xs uppercase tracking-widest mb-1">Negocios activos</p>
                <p class="text-3xl font-bold text-white">{{ $activeBusinesses }}</p>
            </div>
        </div>
    </section>

</div>
@endsection

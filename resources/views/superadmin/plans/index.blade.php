@extends('layouts.superadmin')

@section('header', 'Planes SaaS')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

    {{-- Flash messages --}}
    @if(session('success'))
        <div role="alert" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-500/10 text-emerald-400 ring-1 ring-emerald-500/30 text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div role="alert" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-500/10 text-red-400 ring-1 ring-red-500/30 text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header row --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">Planes SaaS</h1>
            <p class="text-slate-400 text-sm mt-1">Gestiona los planes de suscripción disponibles para los negocios.</p>
        </div>
        <a href="{{ route('superadmin.plans.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-400 text-slate-900 text-sm font-semibold hover:bg-amber-300 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-slate-950">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo plan
        </a>
    </div>

    {{-- Table --}}
    <section aria-labelledby="planes-heading" class="bg-slate-900 rounded-xl ring-1 ring-slate-800 overflow-hidden">
        <h2 id="planes-heading" class="sr-only">Listado de planes</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800" aria-label="Planes SaaS">
                <thead class="bg-slate-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Nombre</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Precio / mes</th>
                        <th scope="col" class="hidden sm:table-cell px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Precio / año</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Mesas</th>
                        <th scope="col" class="hidden md:table-cell px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Personal</th>
                        <th scope="col" class="hidden md:table-cell px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Plantas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Negocios</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-white">{{ $plan->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ number_format($plan->price_monthly ?? $plan->price, 2, ',', '.') }} €</td>
                            <td class="hidden sm:table-cell px-6 py-4 text-sm text-slate-300">
                                {{ $plan->price_yearly ? number_format($plan->price_yearly, 2, ',', '.') . ' €' : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">
                                {{ $plan->max_tables !== null ? $plan->max_tables : '∞' }}
                            </td>
                            <td class="hidden md:table-cell px-6 py-4 text-sm text-slate-300">
                                {{ $plan->max_staff !== null ? $plan->max_staff : '∞' }}
                            </td>
                            <td class="hidden md:table-cell px-6 py-4 text-sm text-slate-300">
                                {{ $plan->max_floors !== null ? $plan->max_floors : '∞' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                             {{ $plan->users_count > 0 ? 'bg-amber-400/10 text-amber-400' : 'bg-slate-700 text-slate-400' }}">
                                    {{ $plan->users_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('superadmin.plans.edit', $plan) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400"
                                       aria-label="Editar plan {{ $plan->name }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>

                                    @if($plan->users_count > 0)
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium
                                                     text-slate-600 bg-slate-800/50 cursor-not-allowed"
                                              title="No se puede eliminar: tiene {{ $plan->users_count }} negocio(s) asignado(s)"
                                              aria-disabled="true">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Eliminar
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('superadmin.plans.destroy', $plan) }}"
                                              onsubmit="return confirm('¿Eliminar el plan «{{ $plan->name }}»? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-red-400 bg-red-500/10 hover:bg-red-500/20 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500"
                                                    aria-label="Eliminar plan {{ $plan->name }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500 text-sm">
                                No hay planes creados todavía.
                                <a href="{{ route('superadmin.plans.create') }}" class="text-amber-400 hover:text-amber-300 ml-1">Crear el primero</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($plans->hasPages())
            <div class="px-6 py-4 border-t border-slate-800">
                <nav aria-label="Paginación de planes">
                    {{ $plans->links() }}
                </nav>
            </div>
        @endif
    </section>

</div>

@endsection

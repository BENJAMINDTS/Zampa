@extends('layouts.superadmin')

@section('header', 'Negocios')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

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
            <h1 class="text-xl sm:text-2xl font-bold text-white">Negocios registrados</h1>
            <p class="text-slate-400 text-sm mt-1">Gestiona los restaurantes y negocios de la plataforma.</p>
        </div>
        <a href="{{ route('superadmin.businesses.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-400 text-slate-900 text-sm font-semibold hover:bg-amber-300 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-slate-950">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo negocio
        </a>
    </div>

    {{-- Table --}}
    <section aria-labelledby="businesses-heading" class="bg-slate-900 rounded-xl ring-1 ring-slate-800 overflow-hidden">
        <h2 id="businesses-heading" class="sr-only">Listado de negocios</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800" aria-label="Negocios registrados">
                <thead class="bg-slate-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Negocio</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Plan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Mesas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Pedidos</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Estado</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($businesses as $business)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-white">{{ $business->business_name ?? $business->name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $business->name }}</div>
                                @if($business->address)
                                    <div class="text-xs text-slate-600 mt-0.5 truncate max-w-[180px]">{{ $business->address }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300 hidden sm:table-cell">{{ $business->email }}</td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                @if($business->plan)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-400/10 text-amber-400">
                                        {{ $business->plan->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-600">Sin plan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300 hidden lg:table-cell">{{ $business->tables_count }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300 hidden lg:table-cell">{{ $business->orders_count }}</td>
                            <td class="px-6 py-4">
                                @if($business->active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400" aria-hidden="true"></span>
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">

                                    {{-- Toggle activo/inactivo --}}
                                    <form method="POST" action="{{ route('superadmin.businesses.toggle', $business) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400
                                                       {{ $business->active
                                                          ? 'text-amber-400 bg-amber-500/10 hover:bg-amber-500/20'
                                                          : 'text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20' }}"
                                                aria-label="{{ $business->active ? 'Desactivar' : 'Activar' }} negocio {{ $business->business_name ?? $business->name }}">
                                            @if($business->active)
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                Desactivar
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Activar
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Editar --}}
                                    <a href="{{ route('superadmin.businesses.edit', $business) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-sky-400 bg-sky-500/10 hover:bg-sky-500/20 transition-colors focus:outline-none focus:ring-2 focus:ring-sky-500"
                                       aria-label="Editar negocio {{ $business->business_name ?? $business->name }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>

                                    {{-- Eliminar --}}
                                    <form method="POST" action="{{ route('superadmin.businesses.destroy', $business) }}"
                                          onsubmit="return confirm('¿Eliminar el negocio «{{ $business->business_name ?? $business->name }}»? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-red-400 bg-red-500/10 hover:bg-red-500/20 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500"
                                                aria-label="Eliminar negocio {{ $business->business_name ?? $business->name }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Eliminar
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 text-sm">
                                No hay negocios registrados todavía.
                                <a href="{{ route('superadmin.businesses.create') }}" class="text-amber-400 hover:text-amber-300 ml-1">Crear el primero</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($businesses->hasPages())
            <div class="px-6 py-4 border-t border-slate-800">
                <nav aria-label="Paginación de negocios">
                    {{ $businesses->links() }}
                </nav>
            </div>
        @endif
    </section>

</div>

@endsection

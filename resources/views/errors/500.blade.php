{{--
 * Error 500 — Error interno del servidor
 *
 * Zampi shaking/dizzy — something broke on our end.
 * NOTE: No route() calls here — app may be in broken state.
 *
 * @author BenjaminDTS
 --}}
@extends('errors.layout')

@section('code', '500')
@section('tag', 'Error del servidor')
@section('title', '¡Zampi se ha caído!')
@section('message', 'Algo ha salido mal en el servidor. Nuestro equipo ya está trabajando en ello. Intenta recargar en unos momentos.')
@section('mood', 'z-mood-shake')
@section('zampi-filter', 'drop-shadow(0 0 24px rgba(239,68,68,0.35)) drop-shadow(0 14px 24px rgba(217,123,26,0.15))')

@section('actions')
    <button onclick="location.reload()" class="z-btn-primary" type="button" aria-label="Recargar la página">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
        </svg>
        Recargar
    </button>
    <a href="/" class="z-btn-ghost" aria-label="Ir al inicio">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Ir al inicio
    </a>
@endsection

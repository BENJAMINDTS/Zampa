{{--
 * Error 419 — Sesión expirada (CSRF token mismatch)
 *
 * Zampi pulsing — time ran out.
 *
 * @author BenjaminDTS
 --}}
@extends('errors.layout')

@section('code', '419')
@section('tag', 'Sesión expirada')
@section('title', '¡La sesión de Zampi caducó!')
@section('message', 'Tu sesión ha expirado por inactividad. Recarga la página para continuar desde donde lo dejaste.')
@section('mood', 'z-mood-pulse')

@section('actions')
    <button onclick="location.reload()" class="z-btn-primary" type="button" aria-label="Recargar la página">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
        </svg>
        Recargar página
    </button>
    <a href="/" class="z-btn-ghost" aria-label="Ir al inicio">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Ir al inicio
    </a>
@endsection

{{--
 * Error 503 — Servicio no disponible (mantenimiento)
 *
 * Zampi breathing slowly — sleeping, waiting for maintenance to end.
 * NOTE: No route() calls — app is in maintenance mode.
 *
 * @author BenjaminDTS
 --}}
@extends('errors.layout')

@section('code', '503')
@section('tag', 'En mantenimiento')
@section('title', 'Zampa está descansando un momento')
@section('message', 'Estamos realizando tareas de mantenimiento para mejorar tu experiencia. Volvemos enseguida.')
@section('mood', 'z-mood-breathe')
@section('zampi-filter', 'drop-shadow(0 0 32px rgba(99,102,241,0.3)) drop-shadow(0 14px 28px rgba(217,123,26,0.2))')

@section('actions')
    <button onclick="location.reload()" class="z-btn-primary" type="button" aria-label="Comprobar si ya está disponible">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
        </svg>
        Comprobar de nuevo
    </button>
@endsection

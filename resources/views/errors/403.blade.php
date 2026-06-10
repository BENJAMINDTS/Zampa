{{--
 * Error 403 — Acceso denegado
 *
 * Zampi with a red warning glow — stern, guarding the entrance.
 *
 * @author BenjaminDTS
 --}}
@extends('errors.layout')

@section('code', '403')
@section('tag', 'Acceso denegado')
@section('title', 'Zampi no te deja pasar')
@section('message', 'No tienes permisos para acceder a este recurso. Si crees que es un error, contacta con el administrador.')
@section('mood', 'z-mood-float')
@section('zampi-filter', 'drop-shadow(0 0 28px rgba(220,38,38,0.45)) drop-shadow(0 14px 24px rgba(217,123,26,0.2))')

@section('actions')
    <button onclick="history.back()" class="z-btn-ghost" type="button" aria-label="Volver a la página anterior">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Volver atrás
    </button>
    @auth
    <a href="{{ route('dashboard') }}" class="z-btn-primary" aria-label="Ir a mi panel">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        Mi panel
    </a>
    @else
    <a href="/" class="z-btn-primary" aria-label="Ir al inicio">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Ir al inicio
    </a>
    @endauth
@endsection

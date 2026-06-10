{{--
 * Error 404 — Página no encontrada
 *
 * Zampi looking confused, swaying side to side.
 *
 * @author BenjaminDTS
 --}}
@extends('errors.layout')

@section('code', '404')
@section('tag', 'Página no encontrada')
@section('title', '¡Zampi no encuentra nada aquí!')
@section('message', 'La página que buscas no existe, fue movida o el enlace está roto. Comprueba la URL o vuelve al inicio.')
@section('mood', 'z-mood-sway')

@section('actions')
    <button onclick="history.back()" class="z-btn-ghost" type="button" aria-label="Volver a la página anterior">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Volver atrás
    </button>
    <a href="/" class="z-btn-primary" aria-label="Ir a la página de inicio">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Ir al inicio
    </a>
@endsection

{{--
 * Error 429 — Too Many Requests
 *
 * Zampi shaking — overwhelmed by all the requests.
 *
 * @author BenjaminDTS
 --}}
@extends('errors.layout')

@section('code', '429')
@section('tag', 'Demasiadas peticiones')
@section('title', '¡Zampi está abrumado!')
@section('message', 'Has enviado demasiadas peticiones en poco tiempo. Espera unos segundos y vuelve a intentarlo.')
@section('mood', 'z-mood-shake')
@section('zampi-filter', 'drop-shadow(0 0 20px rgba(234,179,8,0.35)) drop-shadow(0 14px 24px rgba(217,123,26,0.2))')

@section('actions')
    <button onclick="history.back()" class="z-btn-ghost" type="button" aria-label="Volver a la página anterior">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Volver atrás
    </button>
    <button onclick="setTimeout(()=>location.reload(), 3000); this.textContent='Esperando…'; this.disabled=true;"
            class="z-btn-primary" type="button" aria-label="Reintentar en 3 segundos">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
        </svg>
        Reintentar
    </button>
@endsection

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Añade cabeceras de seguridad HTTP a todas las respuestas.
 *
 * Cubre: CSP, X-Frame-Options, X-Content-Type-Options,
 * Referrer-Policy, Permissions-Policy y HSTS (producción).
 *
 * @author BenjaminDTS
 */
class SecurityHeaders
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(self), payment=(self)'
        );
        $isLocal  = config('app.env') === 'local';
        $viteSrc  = $isLocal ? ' http://127.0.0.1:5173' : '';
        $viteWs   = $isLocal ? ' ws://127.0.0.1:5173'   : '';

        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', array_filter([
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com{$viteSrc}",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com{$viteSrc}",
                "font-src 'self' https://fonts.gstatic.com{$viteSrc}",
                "img-src 'self' data: blob: https:",
                "connect-src 'self' https://api.stripe.com https://api.openai.com https://api.x.ai{$viteSrc}{$viteWs}",
                "frame-src https://js.stripe.com https://hooks.stripe.com",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                $isLocal ? null : "upgrade-insecure-requests",
            ]))
        );

        if (config('app.env') === 'production') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}

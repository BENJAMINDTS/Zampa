<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reflashea los datos flash de sesión en peticiones AJAX.
 *
 * El badge polling del layout (kitchenBadge, barBadge) hace fetch cada 10 s.
 * Laravel envejece el flash en CADA petición web. Si un poll llega entre el
 * redirect del formulario y la carga de la página de destino, el flash queda
 * marcado para eliminar antes de que la vista lo lea → toast nunca aparece.
 *
 * Con este middleware las peticiones XHR "devuelven" el flash al ciclo
 * siguiente en lugar de consumirlo.
 *
 * @author Ayrtonalania
 */
class PreserveFlashForAjax
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->ajax()) {
            session()->reflash();
        }

        return $response;
    }
}

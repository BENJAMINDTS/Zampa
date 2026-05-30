<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Gestiona el flujo de conexión de cuentas Stripe (Stripe Connect Express).
 * Cada restaurante conecta su propia cuenta para recibir los pagos directamente
 * mediante destination charges sin necesidad de pasar por la cuenta de la plataforma.
 *
 * @author BenjaminDTS
 */
class StripeConnectController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    /**
     * Inicia el onboarding de Stripe Connect Express.
     * Crea una cuenta Express si el restaurante no tiene una,
     * genera un Account Link y redirige al formulario de Stripe.
     *
     * @return RedirectResponse
     */
    public function connect(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->stripe_account_id) {
            $account = $this->stripe->createConnectAccount(
                email:        $user->email,
                businessName: $user->business_name,
            );
            $user->update(['stripe_account_id' => $account['id']]);
        }

        $url = $this->stripe->createAccountLink(
            accountId:  $user->stripe_account_id,
            refreshUrl: route('stripe.connect'),
            returnUrl:  route('stripe.connect.callback'),
        );

        return redirect($url);
    }

    /**
     * Callback de Stripe tras completar (o salir de) el onboarding.
     * Consulta el estado real de la cuenta y actualiza el flag de onboarding.
     *
     * @return RedirectResponse
     */
    public function callback(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->stripe_account_id) {
            $status = $this->stripe->getAccountStatus($user->stripe_account_id);
            $user->update(['stripe_onboarding_completed' => $status['charges_enabled']]);
            $user->refresh();
        }

        $message = $user->stripe_onboarding_completed
            ? 'Cuenta de Stripe conectada. Los pagos con tarjeta se ingresarán en tu cuenta bancaria.'
            : 'Onboarding de Stripe pendiente. Completa todos los datos para activar los cobros.';

        return redirect()->route('negocio.config.edit')->with('success', $message);
    }

    /**
     * Desconecta la cuenta de Stripe del restaurante.
     * Los pagos pasarán a ir a la cuenta de la plataforma hasta que se reconecte.
     *
     * @return RedirectResponse
     */
    public function disconnect(): RedirectResponse
    {
        Auth::user()->update([
            'stripe_account_id'           => null,
            'stripe_onboarding_completed' => false,
        ]);

        return redirect()->route('negocio.config.edit')
            ->with('success', 'Cuenta de Stripe desconectada.');
    }
}

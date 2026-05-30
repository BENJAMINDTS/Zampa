<?php

namespace App\Services;

use Stripe\StripeClient;

/**
 * Servicio para interactuar con la API de Stripe.
 * Soporta Stripe Connect Express: cada restaurante puede conectar su propia cuenta
 * y recibir los pagos directamente mediante destination charges.
 * Siempre se debe mockear en tests — nunca hacer llamadas reales.
 *
 * @author BenjaminDTS
 */
class StripeService
{
    private StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Crea un PaymentIntent en Stripe.
     * Si se indica $stripeAccountId, el cobro se enruta a esa cuenta conectada
     * mediante destination charge (los fondos van al restaurante directamente).
     *
     * @param  int          $amount          Importe en céntimos (e.g. 1250 para 12,50 €)
     * @param  string       $currency        Código ISO de la moneda (por defecto 'eur')
     * @param  array        $metadata        Metadatos opcionales para el PaymentIntent
     * @param  string|null  $stripeAccountId ID de cuenta Connect del restaurante (acct_xxx)
     * @return array{id: string, client_secret: string, status: string}
     */
    public function createPaymentIntent(
        int $amount,
        string $currency = 'eur',
        array $metadata = [],
        ?string $stripeAccountId = null,
    ): array {
        $params = [
            'amount'                    => $amount,
            'currency'                  => $currency,
            'metadata'                  => $metadata,
            'automatic_payment_methods' => ['enabled' => true],
        ];

        if ($stripeAccountId) {
            $params['on_behalf_of']  = $stripeAccountId;
            $params['transfer_data'] = ['destination' => $stripeAccountId];
        }

        $intent = $this->client->paymentIntents->create($params);

        return [
            'id'            => $intent->id,
            'client_secret' => $intent->client_secret,
            'status'        => $intent->status,
        ];
    }

    /**
     * Crea un PaymentIntent parcial para cobro partido.
     * Igual que createPaymentIntent pero incluye metadata de split.
     *
     * @param  int          $amount          Importe en céntimos
     * @param  string       $mode            'items' o 'equitative'
     * @param  int          $partNumber      Número de esta parte (1-based)
     * @param  int          $partsTotal      Total de partes de la división
     * @param  array        $metadata        Metadatos adicionales
     * @param  string|null  $stripeAccountId ID de cuenta Connect del restaurante (acct_xxx)
     * @return array{id: string, client_secret: string, status: string}
     */
    public function createSplitPaymentIntent(
        int $amount,
        string $mode,
        int $partNumber,
        int $partsTotal,
        array $metadata = [],
        ?string $stripeAccountId = null,
    ): array {
        $params = [
            'amount'                    => $amount,
            'currency'                  => 'eur',
            'metadata'                  => array_merge($metadata, [
                'split_mode'        => $mode,
                'split_part_number' => $partNumber,
                'split_parts_total' => $partsTotal,
            ]),
            'automatic_payment_methods' => ['enabled' => true],
        ];

        if ($stripeAccountId) {
            $params['on_behalf_of']  = $stripeAccountId;
            $params['transfer_data'] = ['destination' => $stripeAccountId];
        }

        $intent = $this->client->paymentIntents->create($params);

        return [
            'id'            => $intent->id,
            'client_secret' => $intent->client_secret,
            'status'        => $intent->status,
        ];
    }

    /**
     * Recupera un PaymentIntent y devuelve su estado actual.
     * Se usa para verificar el pago en el servidor tras la confirmación del cliente.
     * Con destination charges el PI existe en la cuenta de la plataforma,
     * por lo que no se necesita el ID de cuenta conectada.
     *
     * @param  string  $paymentIntentId
     * @return array{id: string, status: string}
     */
    public function confirmPayment(string $paymentIntentId): array
    {
        $intent = $this->client->paymentIntents->retrieve($paymentIntentId);

        return [
            'id'     => $intent->id,
            'status' => $intent->status,
        ];
    }

    /**
     * Crea una cuenta Express de Stripe Connect para un restaurante.
     * El restaurante completará sus datos bancarios mediante el Account Link.
     *
     * @param  string       $email        Email del gerente del restaurante
     * @param  string|null  $businessName Nombre del negocio (opcional)
     * @return array{id: string}
     */
    public function createConnectAccount(string $email, ?string $businessName = null): array
    {
        $params = [
            'type'         => 'express',
            'email'        => $email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers'     => ['requested' => true],
            ],
        ];

        if ($businessName) {
            $params['business_profile']['name'] = $businessName;
        }

        $account = $this->client->accounts->create($params);

        return ['id' => $account->id];
    }

    /**
     * Genera un enlace de onboarding de Stripe Connect para que el restaurante
     * complete sus datos bancarios en la plataforma de Stripe.
     *
     * @param  string  $accountId   ID de cuenta Express (acct_xxx)
     * @param  string  $refreshUrl  URL a la que redirigir si el enlace expira
     * @param  string  $returnUrl   URL a la que redirigir tras completar el onboarding
     * @return string               URL del formulario de onboarding de Stripe
     */
    public function createAccountLink(string $accountId, string $refreshUrl, string $returnUrl): string
    {
        $link = $this->client->accountLinks->create([
            'account'     => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url'  => $returnUrl,
            'type'        => 'account_onboarding',
        ]);

        return $link->url;
    }

    /**
     * Consulta el estado de una cuenta Connect de Stripe.
     * Devuelve si la cuenta puede cobrar y si ha completado el onboarding.
     *
     * @param  string  $accountId  ID de cuenta Express (acct_xxx)
     * @return array{charges_enabled: bool, details_submitted: bool}
     */
    public function getAccountStatus(string $accountId): array
    {
        $account = $this->client->accounts->retrieve($accountId);

        return [
            'charges_enabled'  => $account->charges_enabled,
            'details_submitted' => $account->details_submitted,
        ];
    }
}

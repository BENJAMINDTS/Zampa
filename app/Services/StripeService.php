<?php

namespace App\Services;

use Stripe\StripeClient;

/**
 * Servicio para interactuar con la API de Stripe.
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
     *
     * @param  int     $amount    Importe en céntimos (e.g. 1250 para 12,50 €)
     * @param  string  $currency  Código ISO de la moneda (por defecto 'eur')
     * @param  array   $metadata  Metadatos opcionales para el PaymentIntent
     * @return array{id: string, client_secret: string, status: string}
     */
    public function createPaymentIntent(int $amount, string $currency = 'eur', array $metadata = []): array
    {
        $intent = $this->client->paymentIntents->create([
            'amount'                    => $amount,
            'currency'                  => $currency,
            'metadata'                  => $metadata,
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        return [
            'id'            => $intent->id,
            'client_secret' => $intent->client_secret,
            'status'        => $intent->status,
        ];
    }

    /**
     * Recupera un PaymentIntent y devuelve su estado actual.
     * Se usa para verificar el pago en el servidor tras la confirmación del cliente.
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
}

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
     * Crea un PaymentIntent parcial para cobro partido.
     * Igual que createPaymentIntent pero incluye metadata de split.
     *
     * @param  int     $amount      Importe en céntimos
     * @param  string  $mode        'items' o 'equitative'
     * @param  int     $partNumber  Número de esta parte (1-based)
     * @param  int     $partsTotal  Total de partes de la división
     * @param  array   $metadata    Metadatos adicionales
     * @return array{id: string, client_secret: string, status: string}
     */
    public function createSplitPaymentIntent(
        int $amount,
        string $mode,
        int $partNumber,
        int $partsTotal,
        array $metadata = [],
    ): array {
        $intent = $this->client->paymentIntents->create([
            'amount'                    => $amount,
            'currency'                  => 'eur',
            'metadata'                  => array_merge($metadata, [
                'split_mode'        => $mode,
                'split_part_number' => $partNumber,
                'split_parts_total' => $partsTotal,
            ]),
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

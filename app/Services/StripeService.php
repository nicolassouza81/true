<?php

declare(strict_types=1);

namespace App\Services;

use Core\Env;
use RuntimeException;

/**
 * Wrapper mínimo sobre a API REST do Stripe usando cURL puro
 * (sem SDK Composer). Cobre Checkout Sessions, Customers e
 * recuperação de Subscriptions — suficiente para o fluxo de
 * assinatura via Stripe Checkout + Webhooks.
 *
 * Docs: https://stripe.com/docs/api
 */
final class StripeService
{
    private const API_BASE = 'https://api.stripe.com/v1';

    private string $secretKey;

    public function __construct(?string $secretKey = null)
    {
        $this->secretKey = $secretKey ?? (string) Env::get('STRIPE_SECRET_KEY', '');

        if ($this->secretKey === '') {
            throw new RuntimeException('STRIPE_SECRET_KEY não configurada no .env');
        }
    }

    /**
     * Cria uma Checkout Session para assinatura recorrente.
     *
     * @param string $priceId Stripe Price ID (recorrente)
     * @param string $customerEmail E-mail do cliente (prefill)
     * @param string $successUrl URL de retorno em caso de sucesso
     * @param string $cancelUrl URL de retorno em caso de cancelamento
     * @param array<string, string> $metadata Metadados (ex: ['user_id' => '123'])
     */
    public function createCheckoutSession(
        string $priceId,
        string $customerEmail,
        string $successUrl,
        string $cancelUrl,
        array $metadata = []
    ): array {
        $params = [
            'mode'                  => 'subscription',
            'customer_email'        => $customerEmail,
            'line_items'            => [
                [
                    'price'    => $priceId,
                    'quantity' => 1,
                ],
            ],
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl,
        ];

        foreach ($metadata as $key => $value) {
            $params['subscription_data']['metadata'][$key] = $value;
        }

        return $this->request('POST', '/checkout/sessions', $params);
    }

    /** Recupera uma Checkout Session pelo ID (ex: na página de retorno). */
    public function retrieveCheckoutSession(string $sessionId): array
    {
        return $this->request('GET', "/checkout/sessions/{$sessionId}");
    }

    /** Recupera uma Subscription pelo ID. */
    public function retrieveSubscription(string $subscriptionId): array
    {
        return $this->request('GET', "/subscriptions/{$subscriptionId}");
    }

    /** Cancela uma Subscription (no fim do período ou imediatamente). */
    public function cancelSubscription(string $subscriptionId, bool $atPeriodEnd = true): array
    {
        if ($atPeriodEnd) {
            return $this->request('POST', "/subscriptions/{$subscriptionId}", [
                'cancel_at_period_end' => 'true',
            ]);
        }

        return $this->request('DELETE', "/subscriptions/{$subscriptionId}");
    }

    /** Cria um Billing Portal Session (gerenciamento de assinatura pelo cliente). */
    public function createBillingPortalSession(string $customerId, string $returnUrl): array
    {
        return $this->request('POST', '/billing_portal/sessions', [
            'customer'   => $customerId,
            'return_url' => $returnUrl,
        ]);
    }

    /**
     * Valida a assinatura do webhook (Stripe-Signature header) e retorna
     * o payload decodificado. Lança RuntimeException se inválido.
     *
     * @return array{id:string, type:string, data:array}
     */
    public function verifyWebhookSignature(string $payload, string $signatureHeader, ?string $webhookSecret = null): array
    {
        $secret = $webhookSecret ?? (string) Env::get('STRIPE_WEBHOOK_SECRET', '');

        if ($secret === '') {
            throw new RuntimeException('STRIPE_WEBHOOK_SECRET não configurado no .env');
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            [$key, $value] = array_pad(explode('=', $segment, 2), 2, '');
            $parts[trim($key)] = trim($value);
        }

        $timestamp = $parts['t'] ?? '';
        $signature = $parts['v1'] ?? '';

        if ($timestamp === '' || $signature === '') {
            throw new RuntimeException('Assinatura do webhook ausente ou malformada.');
        }

        $signedPayload = "{$timestamp}.{$payload}";
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Assinatura do webhook inválida.');
        }

        $decoded = json_decode($payload, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Payload do webhook inválido (JSON malformado).');
        }

        return $decoded;
    }

    /**
     * Executa uma requisição HTTP à API Stripe via cURL.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $params = []): array
    {
        $url = self::API_BASE . $path;

        $ch = curl_init();

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->secretKey,
            ],
        ];

        if ($method === 'GET') {
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
            $options[CURLOPT_URL] = $url;
        } else {
            $options[CURLOPT_URL] = $url;
            $options[CURLOPT_CUSTOMREQUEST] = $method;
            $options[CURLOPT_POSTFIELDS] = $this->buildQuery($params);

            $options[CURLOPT_HTTPHEADER][] = 'Idempotency-Key: ' . $this->idempotencyKey($method, $path, $params);
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("Erro cURL ao chamar Stripe: {$error}");
        }

        $decoded = json_decode((string) $response, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Resposta inválida da API Stripe.');
        }

        if ($httpCode >= 400) {
            $message = $decoded['error']['message'] ?? 'Erro desconhecido na API Stripe.';
            throw new RuntimeException("Stripe API error ({$httpCode}): {$message}");
        }

        return $decoded;
    }

    /**
     * Converte arrays aninhados para o formato esperado pela API Stripe
     * (ex: line_items[0][price]=xxx, subscription_data[metadata][user_id]=1).
     */
    private function buildQuery(array $params, string $prefix = ''): string
    {
        $pairs = [];

        foreach ($params as $key => $value) {
            $formKey = $prefix === '' ? (string) $key : "{$prefix}[{$key}]";

            if (is_array($value)) {
                $pairs[] = $this->buildQuery($value, $formKey);
                continue;
            }

            $pairs[] = urlencode($formKey) . '=' . urlencode((string) $value);
        }

        return implode('&', array_filter($pairs, fn ($p) => $p !== ''));
    }

    /** Gera uma idempotency key estável para a mesma requisição. */
    private function idempotencyKey(string $method, string $path, array $params): string
    {
        return hash('sha256', $method . $path . json_encode($params));
    }
}

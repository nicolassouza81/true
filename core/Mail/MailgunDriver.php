<?php

declare(strict_types=1);

namespace Core\Mail;

use Core\Env;
use RuntimeException;

/**
 * Driver Mailgun usando a API REST via cURL puro (sem SDK Composer).
 *
 * Configuração necessária no .env:
 *   MAILGUN_API_KEY=key-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *   MAILGUN_DOMAIN=mg.seudominio.com.br
 *   MAILGUN_REGION=eu   # ou 'us' (padrão)
 *
 * Regiões:
 *   US → api.mailgun.net
 *   EU → api.eu.mailgun.net
 *
 * Docs: https://documentation.mailgun.com/en/latest/api-sending.html
 */
final class MailgunDriver implements MailDriverInterface
{
    private string $apiKey;
    private string $domain;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) Env::get('MAILGUN_API_KEY', '');
        $this->domain = (string) Env::get('MAILGUN_DOMAIN', '');

        if ($this->apiKey === '' || $this->domain === '') {
            throw new RuntimeException(
                'MAILGUN_API_KEY e MAILGUN_DOMAIN são obrigatórios no .env para usar o driver Mailgun.'
            );
        }

        $region = strtolower((string) Env::get('MAILGUN_REGION', 'us'));
        $this->baseUrl = $region === 'eu'
            ? 'https://api.eu.mailgun.net/v3'
            : 'https://api.mailgun.net/v3';
    }

    public function send(
        string $to,
        string $from,
        string $fromName,
        string $subject,
        string $html
    ): bool {
        $fromHeader = $fromName !== '' ? "{$fromName} <{$from}>" : $from;

        $payload = [
            'from'    => $fromHeader,
            'to'      => $to,
            'subject' => $subject,
            'html'    => $html,
        ];

        $url = "{$this->baseUrl}/{$this->domain}/messages";

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_USERPWD        => 'api:' . $this->apiKey,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("cURL error ao chamar Mailgun: {$error}");
        }

        if ($httpCode !== 200) {
            $decoded = json_decode((string) $response, true);
            $message = $decoded['message'] ?? $response;
            throw new RuntimeException("Mailgun API error ({$httpCode}): {$message}");
        }

        return true;
    }
}

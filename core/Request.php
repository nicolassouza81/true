<?php

declare(strict_types=1);

namespace Core;

/**
 * Encapsula dados da requisição HTTP (GET, POST, JSON, headers, método, URI).
 */
final class Request
{
    private array $query;
    private array $body;
    private array $server;
    private array $params = [];

    public function __construct()
    {
        $this->query  = $_GET;
        $this->server = $_SERVER;
        $this->body   = $this->parseBody();
    }

    private function parseBody(): array
    {
        $contentType = $this->server['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        return $path !== false && $path !== null ? rtrim($path, '/') ?: '/' : '/';
    }

    public function isAjax(): bool
    {
        return strtolower($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    public function isJson(): bool
    {
        return str_contains($this->server['CONTENT_TYPE'] ?? '', 'application/json');
    }

    /** Define parâmetros de rota (id, slug, etc.) capturados pelo Router. */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function params(): array
    {
        return $this->params;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function header(string $key): ?string
    {
        $key = 'HTTP_' . str_replace('-', '_', strtoupper($key));
        return $this->server[$key] ?? null;
    }

    /**
     * Retorna o IP real do cliente.
     *
     * Só confia no header X-Forwarded-For quando a requisição chega de um
     * proxy explicitamente marcado como confiável (TRUSTED_PROXIES no .env).
     * Sem isso, qualquer cliente poderia forjar o header e burlar o
     * RateLimiter (login, forgot-password) e falsificar o IP no audit log.
     */
    public function ip(): string
    {
        $remoteAddr = $this->server['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!self::isTrustedProxy($remoteAddr)) {
            return $remoteAddr;
        }

        $forwardedFor = (string) ($this->server['HTTP_X_FORWARDED_FOR'] ?? '');

        if ($forwardedFor === '') {
            return $remoteAddr;
        }

        // X-Forwarded-For pode conter uma cadeia "cliente, proxy1, proxy2".
        // O IP mais à esquerda é o do cliente original.
        $clientIp = trim(explode(',', $forwardedFor)[0]);

        return filter_var($clientIp, FILTER_VALIDATE_IP) !== false ? $clientIp : $remoteAddr;
    }

    /**
     * Verifica se o IP de origem da requisição está na lista de proxies
     * confiáveis (Env::TRUSTED_PROXIES, separados por vírgula). Configure
     * com o(s) IP(s) do load balancer/CDN em frente à aplicação (ex: Cloudflare,
     * proxy interno da Hostinger). Deixe vazio se a app recebe tráfego direto.
     */
    private static function isTrustedProxy(string $ip): bool
    {
        static $trusted = null;

        if ($trusted === null) {
            $configured = (string) Env::get('TRUSTED_PROXIES', '');
            $trusted = array_filter(array_map('trim', explode(',', $configured)));
        }

        return in_array($ip, $trusted, true);
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization') ?? '';

        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

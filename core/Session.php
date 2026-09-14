<?php

declare(strict_types=1);

namespace Core;

/**
 * Wrapper sobre $_SESSION com suporte a flash messages e CSRF token.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $lifetime = Env::getInt('SESSION_LIFETIME', 120) * 60;

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name((string) Env::get('SESSION_NAME', 'app_session'));
        session_start();
    }

    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /** Define uma mensagem flash (disponível apenas na próxima requisição). */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /** Recupera e remove uma mensagem flash. */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    // ------------------------------------------------------------
    // CSRF
    // ------------------------------------------------------------

    public static function csrfToken(): string
    {
        if (!self::has('_csrf_token')) {
            self::set('_csrf_token', bin2hex(random_bytes(32)));
        }

        return (string) self::get('_csrf_token');
    }

    public static function verifyCsrf(?string $token): bool
    {
        if ($token === null || !self::has('_csrf_token')) {
            return false;
        }

        return hash_equals((string) self::get('_csrf_token'), $token);
    }

    /** Rotaciona o token CSRF (chamar após validação bem-sucedida). */
    public static function regenerateCsrf(): void
    {
        self::set('_csrf_token', bin2hex(random_bytes(32)));
    }
}

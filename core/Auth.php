<?php

declare(strict_types=1);

namespace Core;

use App\Models\UserModel;

/**
 * Helpers de autenticação baseados em sessão.
 *
 * Cache de requisição (static $cachedUser): evita múltiplas queries ao banco
 * por requisição quando check()/user()/isAdmin() são chamados em sequência
 * (nav + middleware + controller).
 */
final class Auth
{
    private const SESSION_KEY     = 'auth_user_id';
    private const SESSION_VERSION = 'auth_session_version';

    /** Cache em memória por requisição — evita queries repetidas. */
    private static ?array $cachedUser = null;
    private static bool   $cacheChecked = false;

    public static function attempt(string $email, string $password): bool
    {
        $model = new UserModel();
        $user  = $model->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password'])) {
            return false;
        }

        if ($user['status'] !== 'active') {
            return false;
        }

        self::login((int) $user['id'], (int) ($user['session_version'] ?? 1));

        return true;
    }

    public static function login(int $userId, int $sessionVersion = 1): void
    {
        self::clearCache();
        Session::regenerate();
        Session::set(self::SESSION_KEY, $userId);
        Session::set(self::SESSION_VERSION, $sessionVersion);
    }

    public static function logout(): void
    {
        self::clearCache();
        Session::remove(self::SESSION_KEY);
        Session::remove(self::SESSION_VERSION);
        Session::regenerate();
    }

    public static function check(): bool
    {
        return self::resolveUser() !== null;
    }

    public static function id(): ?int
    {
        $user = self::resolveUser();
        return $user !== null ? (int) $user['id'] : null;
    }

    /** Retorna dados do usuário autenticado sem password. */
    public static function user(): ?array
    {
        return self::resolveUser();
    }

    public static function isAdmin(): bool
    {
        $user = self::resolveUser();
        return $user !== null && (int) ($user['role_id'] ?? 0) === 1;
    }

    /**
     * Resolve e cacheia o usuário autenticado por toda a requisição.
     * Faz apenas 1 query ao banco, independentemente de quantas vezes for chamado.
     *
     * @return array<string, mixed>|null
     */
    private static function resolveUser(): ?array
    {
        // Já verificou nesta requisição — retorna do cache
        if (self::$cacheChecked) {
            return self::$cachedUser;
        }

        self::$cacheChecked = true;

        if (!Session::has(self::SESSION_KEY)) {
            self::$cachedUser = null;
            return null;
        }

        $userId         = (int) Session::get(self::SESSION_KEY);
        $sessionVersion = (int) Session::get(self::SESSION_VERSION, 1);

        $model = new UserModel();
        $user  = $model->find($userId);

        if ($user === null || $user['status'] !== 'active') {
            self::forceLogout();
            return null;
        }

        if ((int) ($user['session_version'] ?? 1) !== $sessionVersion) {
            self::forceLogout();
            return null;
        }

        unset($user['password']);

        self::$cachedUser = $user;
        return $user;
    }

    /** Limpa o cache interno (chamado em login/logout). */
    public static function clearCache(): void
    {
        self::$cachedUser   = null;
        self::$cacheChecked = false;
    }

    private static function forceLogout(): void
    {
        self::$cachedUser   = null;
        self::$cacheChecked = true; // Evita re-query no mesmo ciclo
        Session::remove(self::SESSION_KEY);
        Session::remove(self::SESSION_VERSION);
    }
}

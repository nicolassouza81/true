<?php

declare(strict_types=1);

namespace Core;

/**
 * Cache simples baseado em arquivos, para uso em shared hosting
 * sem Redis/Memcached. Armazena valor + timestamp de expiração serializados.
 */
final class Cache
{
    private static string $directory = __DIR__ . '/../storage/cache';

    private static function ensureDirectory(): void
    {
        if (!is_dir(self::$directory)) {
            mkdir(self::$directory, 0775, true);
        }
    }

    private static function path(string $key): string
    {
        return self::$directory . '/' . hash('sha256', $key) . '.cache';
    }

    /**
     * Retorna o valor em cache ou executa $callback e armazena o resultado.
     *
     * @param int $ttlSeconds Tempo de vida em segundos
     */
    public static function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $cached = self::get($key);

        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        self::put($key, $value, $ttlSeconds);

        return $value;
    }

    public static function get(string $key): mixed
    {
        self::ensureDirectory();
        $file = self::path($key);

        if (!is_file($file)) {
            return null;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            return null;
        }

        // JSON em vez de serialize()/unserialize(): evita qualquer superfície
        // de PHP object injection caso o diretório de cache um dia deixe de
        // ser exclusivamente escrito pela própria aplicação.
        $data = json_decode($contents, true);

        if (!is_array($data) || !array_key_exists('expires_at', $data) || !array_key_exists('value', $data)) {
            return null;
        }

        if ($data['expires_at'] !== 0 && $data['expires_at'] < time()) {
            @unlink($file);
            return null;
        }

        return $data['value'];
    }

    public static function put(string $key, mixed $value, int $ttlSeconds): void
    {
        self::ensureDirectory();

        $payload = [
            'expires_at' => $ttlSeconds > 0 ? time() + $ttlSeconds : 0,
            'value'      => $value,
        ];

        file_put_contents(self::path($key), json_encode($payload, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public static function forget(string $key): void
    {
        $file = self::path($key);

        if (is_file($file)) {
            @unlink($file);
        }
    }

    public static function flush(): void
    {
        self::ensureDirectory();

        foreach (glob(self::$directory . '/*.cache') ?: [] as $file) {
            @unlink($file);
        }
    }
}

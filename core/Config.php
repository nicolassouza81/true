<?php

declare(strict_types=1);

namespace Core;

/**
 * Carrega e armazena em cache (por requisição) os arquivos de config/*.php.
 *
 * Uso:
 *   Config::get('app.name')
 *   Config::get('app.branding.primary_color')
 *   Config::feature('billing')
 */
final class Config
{
    private static array $cache = [];

    private static function load(string $file): array
    {
        if (isset(self::$cache[$file])) {
            return self::$cache[$file];
        }

        $path = dirname(__DIR__) . "/config/{$file}.php";

        if (!is_file($path)) {
            return self::$cache[$file] = [];
        }

        /** @var array $data */
        $data = require $path;

        return self::$cache[$file] = is_array($data) ? $data : [];
    }

    /**
     * Acessa uma chave usando notação de ponto: "app.branding.primary_color".
     * O primeiro segmento é o nome do arquivo dentro de /config.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        $data = self::load($file);

        foreach ($segments as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }

    /** Verifica se uma feature flag está ativa em config/app.php -> features.* */
    public static function feature(string $name): bool
    {
        return (bool) self::get("app.features.{$name}", false);
    }
}

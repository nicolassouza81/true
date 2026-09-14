<?php

declare(strict_types=1);

/**
 * Autoloader simples no estilo PSR-4, sem Composer.
 *
 * Mapeia namespaces para diretórios:
 *   Core\        -> core/
 *   App\         -> app/
 *
 * Convenção: Core\Foo\Bar -> core/Foo/Bar.php
 */

spl_autoload_register(function (string $class): void {
    $map = [
        'Core\\' => __DIR__ . '/',
        'App\\'  => dirname(__DIR__) . '/app/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $path = $baseDir . str_replace('\\', '/', $relative) . '.php';

        if (is_file($path)) {
            require $path;
            return;
        }
    }
});

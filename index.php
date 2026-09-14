<?php

declare(strict_types=1);

/**
 * Front Controller — ponto de entrada único da aplicação.
 * Toda requisição passa por aqui via .htaccess.
 */

// Suporte nativo ao servidor embutido do PHP (php -S localhost:8000 index.php)
if (php_sapi_name() === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    if ($uri !== '/' && is_file(__DIR__ . $uri)) {
        return false;
    }
}

define('ROOT_PATH', __DIR__);
define('APP_START', microtime(true));

// 1. Autoloader
require __DIR__ . '/core/Autoloader.php';

// 2. Variáveis de ambiente
\Core\Env::load(__DIR__ . '/.env');

// 3. Configurações de runtime
$debug = \Core\Env::getBool('APP_DEBUG', false);
$env   = (string) \Core\Env::get('APP_ENV', 'local');

ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/storage/logs/php_errors.log');
error_reporting(E_ALL);

// 4. Timezone
date_default_timezone_set(
    (string) \Core\Config::get('app.locale.timezone', 'America/Sao_Paulo')
);

// 5. Sessão
\Core\Session::start();

// 6. Headers de segurança (todas as respostas)
if (!headers_sent()) {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");

    if (!$debug) {
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';");
    }
}

// 7. Handler global de erros/exceções
set_exception_handler(function (Throwable $e) use ($debug): void {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }

    if ($debug) {
        echo '<pre style="font-family:monospace;padding:1.5rem;background:#1a1a2e;color:#e2e6ea;margin:0;min-height:100vh">';
        echo '<strong style="color:#dc3545">' . get_class($e) . '</strong>: ';
        echo htmlspecialchars($e->getMessage()) . "\n\n";
        echo '<strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n\n";
        echo '<strong>Trace:</strong>' . "\n" . htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        $errorPage = __DIR__ . '/public/errors/500.php';
        if (is_file($errorPage)) {
            require $errorPage;
        } else {
            echo '<h1>500 — Erro interno</h1><p>Tente novamente em instantes.</p>';
        }
    }

    exit;
});

// 8. Roteamento
$router  = new \Core\Router();
$request = new \Core\Request();

$registerRoutes = require __DIR__ . '/config/routes.php';
$registerRoutes($router);

$router->dispatch($request);

<?php

declare(strict_types=1);

namespace Core;

/**
 * Helpers para respostas HTTP: views, JSON, redirects, status codes.
 */
final class Response
{
    public static function status(int $code): void
    {
        http_response_code($code);
    }

    public static function json(mixed $data, int $status = 200): never
    {
        self::status($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $url, int $status = 302): never
    {
        self::status($status);
        header("Location: {$url}");
        exit;
    }

    /**
     * Renderiza uma view dentro do layout principal.
     *
     * @param string $view Caminho relativo dentro de /views, sem extensão (ex: "auth/login")
     * @param array  $data Variáveis disponíveis na view
     * @param string $layout Nome do layout dentro de /views/layouts (sem extensão)
     */
    public static function view(string $view, array $data = [], string $layout = 'main'): never
    {
        $viewsPath = dirname(__DIR__) . '/views';
        $viewFile = "{$viewsPath}/{$view}.php";
        $layoutFile = "{$viewsPath}/layouts/{$layout}.php";

        if (!is_file($viewFile)) {
            self::status(500);
            echo "View não encontrada: {$view}";
            exit;
        }

        extract($data, EXTR_SKIP);

        $renderView = static function () use ($viewFile, $data): string {
            extract($data, EXTR_SKIP);
            ob_start();
            require $viewFile;
            return (string) ob_get_clean();
        };

        if (is_file($layoutFile)) {
            $content = $renderView();
            require $layoutFile;
        } else {
            echo $renderView();
        }

        exit;
    }

    /** Renderiza apenas um fragmento (sem layout) — útil para respostas AJAX/HTML parcial. */
    public static function partial(string $view, array $data = []): never
    {
        $viewFile = dirname(__DIR__) . "/views/{$view}.php";

        if (!is_file($viewFile)) {
            self::status(500);
            echo "View não encontrada: {$view}";
            exit;
        }

        extract($data, EXTR_SKIP);
        require $viewFile;
        exit;
    }

    public static function abort(int $status, string $message = ''): never
    {
        self::status($status);

        $errorPage = dirname(__DIR__) . "/public/errors/{$status}.php";

        if (is_file($errorPage)) {
            require $errorPage;
            exit;
        }

        echo $message !== '' ? $message : match ($status) {
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            419 => 'CSRF Token Mismatch',
            500 => 'Internal Server Error',
            default => 'Error',
        };

        exit;
    }
}

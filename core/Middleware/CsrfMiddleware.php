<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Valida o token CSRF em requisições de escrita (POST/PUT/PATCH/DELETE)
 * e rotaciona o token após validação (proteção contra replay).
 *
 * Webhooks (ex: Stripe) devem ser excluídos — validam por assinatura própria.
 */
final class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request): void
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return;
        }

        $token = $request->input('_csrf')
            ?? $request->header('X-CSRF-Token');

        if (!Session::verifyCsrf($token)) {
            if ($request->isAjax() || $request->isJson()) {
                Response::json(['message' => 'Token CSRF inválido ou expirado.'], 419);
            }

            Response::abort(419, 'Token CSRF inválido ou expirado. Volte e tente novamente.');
        }

        // Rotaciona o token após uso bem-sucedido (proteção contra replay)
        Session::regenerateCsrf();
    }
}

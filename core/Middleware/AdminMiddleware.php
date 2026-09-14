<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;

/**
 * Garante que o usuário autenticado possua role de administrador.
 * Deve ser usado em conjunto com AuthMiddleware (executado antes).
 */
final class AdminMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }

        if (!Auth::isAdmin()) {
            if ($request->isAjax() || $request->isJson()) {
                Response::json(['message' => 'Acesso negado.'], 403);
            }

            Response::abort(403, 'Acesso restrito a administradores.');
        }
    }
}

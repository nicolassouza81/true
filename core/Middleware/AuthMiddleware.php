<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Garante que o usuário esteja autenticado. Caso contrário,
 * redireciona para o login (ou retorna 401 em requisições JSON/AJAX).
 */
final class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            return;
        }

        if ($request->isAjax() || $request->isJson()) {
            Response::json(['message' => 'Não autenticado.'], 401);
        }

        Session::flash('error', 'Você precisa fazer login para continuar.');
        Response::redirect('/login');
    }
}

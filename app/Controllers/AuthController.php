<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\QueryBuilder;
use App\Models\UserModel;
use Core\Auth;
use Core\Config;
use Core\Mailer;
use Core\RateLimiter;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

/**
 * Controla registro, login, logout e recuperação de senha.
 */
final class AuthController
{
    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/dashboard');
        }

        Response::view('auth/login', [
            'title' => 'Entrar',
            'error' => Session::getFlash('error'),
            'old'   => Session::getFlash('old', []),
        ], 'auth');
    }

    public function login(Request $request): void
    {
        $email    = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $validator = new Validator($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Session::flash('old', ['email' => $email]);
            Response::redirect('/login');
        }

        $limiter    = new RateLimiter();
        $identifier = $request->ip() . '|' . $email;

        if (Config::feature('rate_limit') && $limiter->tooManyAttempts($identifier, 'login')) {
            $seconds = $limiter->availableInSeconds($identifier, 'login');
            Session::flash('error', 'Muitas tentativas. Tente novamente em ' . (int) ceil($seconds / 60) . ' minuto(s).');
            Response::redirect('/login');
        }

        if (!Auth::attempt($email, $password)) {
            if (Config::feature('rate_limit')) {
                $limiter->hit($identifier, 'login');
            }
            Session::flash('error', 'E-mail ou senha inválidos.');
            Session::flash('old', ['email' => $email]);
            Response::redirect('/login');
        }

        if (Config::feature('rate_limit')) {
            $limiter->clear($identifier, 'login');
        }

        if (Config::feature('audit_log')) {
            (new AuditLogModel())->log(Auth::id(), 'auth.login', 'users', Auth::id(), null, $request);
        }

        Response::redirect('/dashboard');
    }

    public function showRegister(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/dashboard');
        }

        Response::view('auth/register', [
            'title' => 'Criar conta',
            'error' => Session::getFlash('error'),
            'old'   => Session::getFlash('old', []),
        ]);
    }

    public function register(Request $request): void
    {
        $data = $request->only(['name', 'email', 'password', 'password_confirmation']);

        $validator = new Validator($data, [
            'name'     => ['required', 'max:120'],
            'email'    => ['required', 'email', 'max:150'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Session::flash('old', ['name' => $data['name'] ?? '', 'email' => $data['email'] ?? '']);
            Response::redirect('/register');
        }

        // Rate limit por IP para evitar criação de contas em massa / spam de e-mail
        $limiter    = new RateLimiter(10, 60);
        $identifier = $request->ip();

        if (Config::feature('rate_limit') && $limiter->tooManyAttempts($identifier, 'register')) {
            Session::flash('error', 'Muitas tentativas de cadastro. Tente novamente mais tarde.');
            Session::flash('old', ['name' => $data['name'] ?? '', 'email' => $data['email'] ?? '']);
            Response::redirect('/register');
        }

        if (Config::feature('rate_limit')) {
            $limiter->hit($identifier, 'register');
        }

        $userModel = new UserModel();

        // Mensagem genérica — não revela se o e-mail já existe
        if ($userModel->emailExists($data['email'])) {
            Session::flash('error', 'Não foi possível criar a conta com os dados informados.');
            Session::flash('old', ['name' => $data['name']]);
            Response::redirect('/register');
        }

        $name   = Validator::sanitize($data['name']);
        $email  = strtolower(trim($data['email']));

        try {
            $userId = $userModel->createUser([
                'name'     => $name,
                'email'    => $email,
                'password' => $data['password'],
                'role_id'  => 2,
            ]);
        } catch (\PDOException $e) {
            // Corrida rara: dois cadastros simultâneos com o mesmo e-mail —
            // o UNIQUE do banco protege, aqui só evitamos vazar um 500.
            Session::flash('error', 'Não foi possível criar a conta com os dados informados.');
            Session::flash('old', ['name' => $name]);
            Response::redirect('/register');
        }

        if (Config::feature('audit_log')) {
            (new AuditLogModel())->log($userId, 'auth.register', 'users', $userId, null, $request);
        }

        // E-mail de boas-vindas (falha silenciosa — não bloqueia o registro)
        try {
            (new Mailer())->sendWelcome($email, $name);
        } catch (\Throwable) {
        }

        Auth::login($userId);
        Response::redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        if (Config::feature('audit_log')) {
            (new AuditLogModel())->log(Auth::id(), 'auth.logout', 'users', Auth::id(), null, $request);
        }

        Auth::logout();
        Response::redirect('/login');
    }

    public function showForgotPassword(Request $request): void
    {
        Response::view('auth/forgot_password', [
            'title'   => 'Recuperar senha',
            'error'   => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ], 'auth');
    }

    public function forgotPassword(Request $request): void
    {
        $email = strtolower(trim((string) $request->input('email', '')));

        $validator = new Validator(['email' => $email], [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('/forgot-password');
        }

        // Rate limit por IP para evitar abuso
        $limiter    = new RateLimiter(3, 60);
        $identifier = $request->ip();

        if (Config::feature('rate_limit') && $limiter->tooManyAttempts($identifier, 'forgot_password')) {
            Session::flash('success', 'Se o e-mail existir em nossa base, você receberá as instruções em breve.');
            Response::redirect('/forgot-password');
        }

        if (Config::feature('rate_limit')) {
            $limiter->hit($identifier, 'forgot_password');
        }

        $userModel = new UserModel();
        $user      = $userModel->findByEmail($email);

        // Sempre exibe mensagem genérica (evita enumeração de e-mails)
        if ($user !== null) {
            $token     = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);

            (new QueryBuilder('password_resets'))->insert([
                'email'      => $email,
                'token_hash' => $tokenHash,
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            ]);

            try {
                (new Mailer())->sendPasswordReset($email, (string) $user['name'], $token);
            } catch (\Throwable $e) {
                error_log('Falha ao enviar e-mail de reset: ' . $e->getMessage());
            }
        }

        Session::flash('success', 'Se o e-mail existir em nossa base, você receberá as instruções em breve.');
        Response::redirect('/forgot-password');
    }

    public function showResetPassword(Request $request): void
    {
        Response::view('auth/reset_password', [
            'title' => 'Redefinir senha',
            'token' => $request->query('token', ''),
            'email' => $request->query('email', ''),
            'error' => Session::getFlash('error'),
        ], 'auth');
    }

    public function resetPassword(Request $request): void
    {
        $data = $request->only(['token', 'email', 'password', 'password_confirmation']);

        $validator = new Validator($data, [
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect(
                '/reset-password?token=' . urlencode($data['token'] ?? '') .
                '&email=' . urlencode($data['email'] ?? '')
            );
        }

        $tokenHash = hash('sha256', (string) $data['token']);
        $resets    = new QueryBuilder('password_resets');

        $reset = $resets->all([
            'email'      => $data['email'],
            'token_hash' => $tokenHash,
        ], 'id', 'DESC', 1)[0] ?? null;

        if ($reset === null || $reset['used_at'] !== null || strtotime($reset['expires_at']) < time()) {
            Session::flash('error', 'Link inválido ou expirado. Solicite um novo.');
            Response::redirect('/forgot-password');
        }

        $userModel = new UserModel();
        $user      = $userModel->findByEmail((string) $data['email']);

        if ($user === null) {
            Session::flash('error', 'Link inválido ou expirado. Solicite um novo.');
            Response::redirect('/forgot-password');
        }

        $userModel->updatePassword((int) $user['id'], (string) $data['password']);
        $resets->update((int) $reset['id'], ['used_at' => date('Y-m-d H:i:s')]);

        if (Config::feature('audit_log')) {
            (new AuditLogModel())->log((int) $user['id'], 'auth.password_reset', 'users', (int) $user['id'], null, $request);
        }

        Session::flash('success', 'Senha redefinida com sucesso. Faça login.');
        Response::redirect('/login');
    }
}

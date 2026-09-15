<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

/**
 * Permite que o usuário autenticado edite seu próprio perfil
 * (nome e senha). E-mail não é editável para evitar conflitos
 * com outros usuários e com o Stripe.
 */
final class ProfileController
{
    public function show(Request $request): void
    {
        Response::view('profile/view_profile', [
            'title' => 'Meu perfil',
            'user'  => Auth::user(),
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $user = Auth::user();

        if ($user === null) {
            Response::redirect('/login');
        }

        $data = $request->only(['name']);

        $validator = new Validator($data, [
            'name' => ['required', 'max:120'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('/profile');
        }

        $userModel = new UserModel();
        $userModel->update((int) $user['id'], [
            'name' => Validator::sanitize($data['name']),
        ]);

        Auth::clearCache();

        Session::flash('success', 'Perfil atualizado com sucesso.');
        Response::redirect('/profile');
    }

    public function updatePassword(Request $request): void
    {
        $user = Auth::user();

        if ($user === null) {
            Response::redirect('/login');
        }

        $data = $request->only(['current_password', 'password', 'password_confirmation']);

        $validator = new Validator($data, [
            'current_password' => ['required'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('/profile');
        }

        $userModel = new UserModel();
        $full      = $userModel->find((int) $user['id']);

        if ($full === null || !password_verify($data['current_password'], $full['password'])) {
            Session::flash('error', 'Senha atual incorreta.');
            Response::redirect('/profile');
        }

        $userModel->updatePassword((int) $user['id'], $data['password']);

        // Atualiza a session_version na sessão para não deslogar o próprio usuário
        $updated = $userModel->find((int) $user['id']);
        Auth::clearCache();
        Session::set('auth_session_version', (int) ($updated['session_version'] ?? 1));

        Session::flash('success', 'Senha alterada com sucesso.');
        Response::redirect('/profile');
    }
}

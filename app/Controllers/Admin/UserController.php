<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\RoleModel;
use App\Models\UserModel;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

/**
 * Gestão de usuários no painel admin. Não usa CrudController genérico
 * pois lida com senha (hash) e relacionamento com roles.
 */
final class UserController
{
    private const ADMIN_ROLE_ID = 1;

    /**
     * Quantos usuários com role de admin e status ativo existem,
     * opcionalmente excluindo um ID (para checar "sobra algum admin
     * além deste?" antes de rebaixar/excluir/desativar).
     */
    private function activeAdminCount(?int $excludingId = null): int
    {
        $admins = (new UserModel())->all(['role_id' => self::ADMIN_ROLE_ID, 'status' => 'active']);

        if ($excludingId !== null) {
            $admins = array_filter($admins, static fn (array $u) => (int) $u['id'] !== $excludingId);
        }

        return count($admins);
    }
    public function index(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $userModel = new UserModel();
        $result = $userModel->paginate($page, 20);

        Response::view('admin/users/index', [
            'title' => 'Usuários',
            'users' => $result['data'],
            'page'  => $result['page'],
            'pages' => $result['pages'],
        ], 'admin');
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->param('id');
        $user = (new UserModel())->findSafe($id);

        if ($user === null) {
            Response::abort(404);
        }

        Response::view('admin/users/form', [
            'title' => 'Editar usuário',
            'user'  => $user,
            'roles' => (new RoleModel())->all(),
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $id = (int) $request->param('id');
        $data = $request->only(['name', 'email', 'role_id', 'status']);

        $validator = new Validator($data, [
            'name'    => ['required', 'max:120'],
            'email'   => ['required', 'email', 'max:150'],
            'role_id' => ['required', 'numeric'],
            'status'  => ['required', 'in:active,inactive,banned'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect("/admin/users/{$id}/edit");
        }

        $userModel = new UserModel();
        $current   = $userModel->find($id);

        if ($current === null) {
            Response::abort(404);
        }

        $newRoleId = (int) $data['role_id'];
        $newStatus = (string) $data['status'];

        $wasAdmin = (int) $current['role_id'] === self::ADMIN_ROLE_ID && $current['status'] === 'active';
        $staysAdmin = $newRoleId === self::ADMIN_ROLE_ID && $newStatus === 'active';

        if ($wasAdmin && !$staysAdmin) {
            if ($id === Auth::id()) {
                Session::flash('error', 'Você não pode remover seu próprio acesso de administrador.');
                Response::redirect("/admin/users/{$id}/edit");
            }

            if ($this->activeAdminCount(excludingId: $id) === 0) {
                Session::flash('error', 'Não é possível remover o último administrador ativo.');
                Response::redirect("/admin/users/{$id}/edit");
            }
        }

        $userModel->update($id, [
            'name'    => Validator::sanitize($data['name']),
            'email'   => strtolower(trim($data['email'])),
            'role_id' => $newRoleId,
            'status'  => $newStatus,
        ]);

        $newPassword = trim((string) $request->input('password', ''));

        if ($newPassword !== '') {
            if (mb_strlen($newPassword) < 8) {
                Session::flash('error', 'A nova senha deve ter no mínimo 8 caracteres.');
                Response::redirect("/admin/users/{$id}/edit");
            }

            $userModel->updatePassword($id, $newPassword);
        }

        Session::flash('success', 'Usuário atualizado com sucesso.');
        Response::redirect('/admin/users');
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');

        if ($id === Auth::id()) {
            Session::flash('error', 'Você não pode excluir sua própria conta.');
            Response::redirect('/admin/users');
        }

        $userModel = new UserModel();
        $target    = $userModel->find($id);

        if ($target === null) {
            Response::abort(404);
        }

        $isActiveAdmin = (int) $target['role_id'] === self::ADMIN_ROLE_ID && $target['status'] === 'active';

        if ($isActiveAdmin && $this->activeAdminCount(excludingId: $id) === 0) {
            Session::flash('error', 'Não é possível excluir o último administrador ativo.');
            Response::redirect('/admin/users');
        }

        $userModel->delete($id);

        Session::flash('success', 'Usuário removido.');
        Response::redirect('/admin/users');
    }
}

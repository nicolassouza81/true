<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\CompanyLogoModel;
use Core\Cache;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

final class CompanyLogoController
{
    public function index(Request $request): void
    {
        $model = new CompanyLogoModel();

        Response::view('admin/logos/index', [
            'title' => 'Logos de empresas',
            'logos' => $model->all([], 'sort_order', 'ASC'),
        ], 'admin');
    }

    public function create(Request $request): void
    {
        Response::view('admin/logos/form', [
            'title' => 'Novo logo',
            'item'  => null,
            'error' => Session::getFlash('error'),
            'old'   => Session::getFlash('old', []),
        ], 'admin');
    }

    public function store(Request $request): void
    {
        $this->persist($request, null);
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->param('id');
        $item = (new CompanyLogoModel())->find($id);

        if ($item === null) {
            Response::abort(404);
        }

        Response::view('admin/logos/form', [
            'title' => 'Editar logo',
            'item'  => $item,
            'error' => Session::getFlash('error'),
            'old'   => Session::getFlash('old', []),
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $this->persist($request, (int) $request->param('id'));
    }

    public function destroy(Request $request): void
    {
        (new CompanyLogoModel())->delete((int) $request->param('id'));
        Cache::forget('home.logos');
        Session::flash('success', 'Logo removido.');
        Response::redirect('/admin/logos');
    }

    private function persist(Request $request, ?int $id): void
    {
        $data = $request->only(['name', 'logo_url', 'website_url', 'alt_text', 'is_active', 'sort_order']);

        $validator = new Validator($data, [
            'name'        => ['required', 'max:120'],
            'logo_url'    => ['required', 'image_url'],
            'website_url' => ['url'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Session::flash('old', $data);
            Response::redirect($id === null ? '/admin/logos/create' : "/admin/logos/{$id}/edit");
        }

        $payload = [
            'name'        => Validator::sanitize($data['name']),
            'logo_url'    => Validator::sanitize($data['logo_url']),
            'website_url' => Validator::sanitize($data['website_url'] ?? ''),
            'alt_text'    => Validator::sanitize($data['alt_text'] ?? ''),
            'is_active'   => isset($data['is_active']) ? 1 : 0,
            'sort_order'  => (int) ($data['sort_order'] ?? 0),
        ];

        $model = new CompanyLogoModel();

        if ($id === null) {
            $model->insert($payload);
            Session::flash('success', 'Logo criado.');
        } else {
            $model->update($id, $payload);
            Session::flash('success', 'Logo atualizado.');
        }

        Cache::forget('home.logos');
        Response::redirect('/admin/logos');
    }
}

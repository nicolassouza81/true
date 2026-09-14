<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\TestimonialModel;
use Core\Cache;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

final class TestimonialController
{
    public function index(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $model = new TestimonialModel();

        Response::view('admin/testimonials/index', [
            'title'        => 'Testemunhos',
            'testimonials' => $model->all([], 'sort_order', 'ASC', 20, ($page - 1) * 20),
            'total'        => $model->count(),
            'page'         => $page,
            'pages'        => (int) ceil($model->count() / 20),
        ], 'admin');
    }

    public function create(Request $request): void
    {
        Response::view('admin/testimonials/form', [
            'title'       => 'Novo testemunho',
            'item'        => null,
            'error'       => Session::getFlash('error'),
            'old'         => Session::getFlash('old', []),
        ], 'admin');
    }

    public function store(Request $request): void
    {
        $this->persist($request, null);
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->param('id');
        $item = (new TestimonialModel())->find($id);

        if ($item === null) {
            Response::abort(404);
        }

        Response::view('admin/testimonials/form', [
            'title' => 'Editar testemunho',
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
        (new TestimonialModel())->delete((int) $request->param('id'));
        Cache::forget('home.testimonials');
        Session::flash('success', 'Testemunho removido.');
        Response::redirect('/admin/testimonials');
    }

    private function persist(Request $request, ?int $id): void
    {
        $data = $request->only(['author_name', 'author_title', 'author_avatar', 'content', 'rating', 'is_active', 'sort_order']);

        $validator = new Validator($data, [
            'author_name'   => ['required', 'max:120'],
            'author_title'  => ['max:120'],
            'author_avatar' => ['image_url'],
            'content'       => ['required', 'max:1000'],
            'rating'        => ['numeric'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Session::flash('old', $data);
            Response::redirect($id === null ? '/admin/testimonials/create' : "/admin/testimonials/{$id}/edit");
        }

        $payload = [
            'author_name'   => Validator::sanitize($data['author_name']),
            'author_title'  => Validator::sanitize($data['author_title'] ?? ''),
            'author_avatar' => Validator::sanitize($data['author_avatar'] ?? ''),
            'content'       => Validator::sanitize($data['content']),
            'rating'        => min(5, max(1, (int) ($data['rating'] ?? 5))),
            'is_active'     => isset($data['is_active']) ? 1 : 0,
            'sort_order'    => (int) ($data['sort_order'] ?? 0),
        ];

        $model = new TestimonialModel();

        if ($id === null) {
            $model->insert($payload);
            Session::flash('success', 'Testemunho criado.');
        } else {
            $model->update($id, $payload);
            Session::flash('success', 'Testemunho atualizado.');
        }

        Cache::forget('home.testimonials');
        Response::redirect('/admin/testimonials');
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\QueryBuilder;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

/**
 * Controller CRUD genérico e abstrato. Extenda esta classe para criar
 * rapidamente telas administrativas de qualquer tabela.
 *
 * Exemplo de uso:
 *
 *   final class PlanAdminController extends CrudController
 *   {
 *       protected string $table = 'plans';
 *       protected string $viewPrefix = 'admin/plans';
 *       protected string $routePrefix = '/admin/plans';
 *
 *       protected function fields(): array
 *       {
 *           return [
 *               'name'  => ['label' => 'Nome', 'rules' => ['required', 'max:100']],
 *               'slug'  => ['label' => 'Slug', 'rules' => ['required', 'alpha_dash']],
 *               'price_cents' => ['label' => 'Preço (centavos)', 'rules' => ['required', 'numeric']],
 *           ];
 *       }
 *   }
 */
abstract class CrudController
{
    protected string $table;
    protected string $viewPrefix;
    protected string $routePrefix;
    protected string $orderBy = 'id';
    protected string $orderDirection = 'DESC';
    protected int $perPage = 20;

    /**
     * Define os campos do formulário/listagem.
     *
     * @return array<string, array{label:string, rules?:array<int,string>}>
     */
    abstract protected function fields(): array;

    protected function model(): QueryBuilder
    {
        return new QueryBuilder($this->table);
    }

    public function index(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $this->perPage;

        $model = $this->model();
        $items = $model->all([], $this->orderBy, $this->orderDirection, $this->perPage, $offset);
        $total = $model->count();

        Response::view("{$this->viewPrefix}/index", [
            'title'       => 'Gerenciar',
            'items'       => $items,
            'fields'      => $this->fields(),
            'routePrefix' => $this->routePrefix,
            'page'        => $page,
            'pages'       => (int) ceil($total / $this->perPage),
        ], 'admin');
    }

    public function create(Request $request): void
    {
        Response::view("{$this->viewPrefix}/form", [
            'title'       => 'Novo registro',
            'fields'      => $this->fields(),
            'item'        => null,
            'routePrefix' => $this->routePrefix,
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
        $item = $this->model()->find($id);

        if ($item === null) {
            Response::abort(404);
        }

        Response::view("{$this->viewPrefix}/form", [
            'title'       => 'Editar registro',
            'fields'      => $this->fields(),
            'item'        => $item,
            'routePrefix' => $this->routePrefix,
            'error'       => Session::getFlash('error'),
            'old'         => Session::getFlash('old', []),
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $id = (int) $request->param('id');
        $this->persist($request, $id);
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');
        $this->model()->delete($id);

        Session::flash('success', 'Registro excluído com sucesso.');
        Response::redirect($this->routePrefix);
    }

    private function persist(Request $request, ?int $id): void
    {
        $fields = $this->fields();
        $rules = [];
        $data = [];

        foreach ($fields as $key => $definition) {
            $rules[$key] = $definition['rules'] ?? [];
            $data[$key] = $request->input($key);
        }

        $validator = new Validator($data, $rules);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Session::flash('old', $data);

            $redirect = $id === null
                ? "{$this->routePrefix}/create"
                : "{$this->routePrefix}/{$id}/edit";

            Response::redirect($redirect);
        }

        // Sanitiza strings
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = Validator::sanitize($value);
            }
        }

        if ($id === null) {
            $this->model()->insert($data);
            Session::flash('success', 'Registro criado com sucesso.');
        } else {
            $this->model()->update($id, $data);
            Session::flash('success', 'Registro atualizado com sucesso.');
        }

        Response::redirect($this->routePrefix);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\LeadModel;
use App\Models\PipelineStageModel;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use PDOException;

/**
 * CRUD das etapas do funil (colunas do Kanban em /admin/pipeline).
 * Não usa o CrudController genérico porque precisa: (a) tratar os
 * checkboxes is_won/is_lost, que não vêm no POST quando desmarcados;
 * (b) impedir a exclusão de uma etapa que ainda tem leads (FK RESTRICT).
 */
final class PipelineStageController
{
    public function index(Request $request): void
    {
        $model = new PipelineStageModel();
        $leadModel = new LeadModel();

        $stages = $model->allOrdered();
        foreach ($stages as &$stage) {
            $stage['lead_count'] = $leadModel->count(['stage_id' => (int) $stage['id']]);
        }
        unset($stage);

        Response::view('admin/pipeline/stages/index', [
            'title'  => 'Etapas do funil',
            'stages' => $stages,
        ], 'admin');
    }

    public function create(Request $request): void
    {
        Response::view('admin/pipeline/stages/form', [
            'title' => 'Nova etapa',
            'item'  => null,
            'error' => Session::getFlash('error'),
            'old'   => Session::getFlash('old', ['sort_order' => (new PipelineStageModel())->nextSortOrder()]),
        ], 'admin');
    }

    public function store(Request $request): void
    {
        $this->persist($request, null);
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->param('id');
        $item = (new PipelineStageModel())->find($id);

        if ($item === null) {
            Response::abort(404);
        }

        Response::view('admin/pipeline/stages/form', [
            'title' => 'Editar etapa',
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
        $id = (int) $request->param('id');
        $leadCount = (new LeadModel())->count(['stage_id' => $id]);

        if ($leadCount > 0) {
            Session::flash('error', "Não é possível excluir: existem {$leadCount} lead(s) nesta etapa. Mova-os para outra etapa antes.");
            Response::redirect('/admin/pipeline/stages');
        }

        try {
            (new PipelineStageModel())->delete($id);
        } catch (PDOException) {
            // Cinto de segurança extra além da checagem acima (ex: corrida entre duas abas)
            Session::flash('error', 'Não é possível excluir esta etapa: ainda há leads vinculados a ela.');
            Response::redirect('/admin/pipeline/stages');
        }

        Session::flash('success', 'Etapa excluída com sucesso.');
        Response::redirect('/admin/pipeline/stages');
    }

    private function persist(Request $request, ?int $id): void
    {
        $data = $request->only(['name', 'color', 'sort_order', 'is_won', 'is_lost']);

        $validator = new Validator($data, [
            'name'       => ['required', 'max:80'],
            'color'      => ['required', 'hex_color'],
            'sort_order' => ['numeric'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Session::flash('old', $data);
            Response::redirect($id === null ? '/admin/pipeline/stages/create' : "/admin/pipeline/stages/{$id}/edit");
        }

        $payload = [
            'name'       => Validator::sanitize($data['name']),
            'color'      => strtolower((string) $data['color']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_won'     => isset($data['is_won']) ? 1 : 0,
            'is_lost'    => isset($data['is_lost']) ? 1 : 0,
        ];

        $model = new PipelineStageModel();

        if ($id === null) {
            $model->insert($payload);
            Session::flash('success', 'Etapa criada com sucesso.');
        } else {
            $model->update($id, $payload);
            Session::flash('success', 'Etapa atualizada com sucesso.');
        }

        Response::redirect('/admin/pipeline/stages');
    }
}

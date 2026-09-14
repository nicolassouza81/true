<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\LeadModel;
use App\Models\LeadStageHistoryModel;
use App\Models\PipelineStageModel;
use Core\Auth;
use Core\Config;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

/**
 * Funil de vendas: quadro Kanban (arrastar card entre etapas) + CRUD dos
 * leads. As etapas em si são gerenciadas pelo PipelineStageController.
 */
final class PipelineController
{
    public function board(Request $request): void
    {
        $stageModel = new PipelineStageModel();
        $leadModel  = new LeadModel();

        $stages = $stageModel->allOrdered();

        // Se não houver etapas, $stages é [] e o foreach abaixo simplesmente
        // não roda — a view já trata o caso de quadro vazio.
        $columns = [];
        foreach ($stages as $stage) {
            $columns[(int) $stage['id']] = $leadModel->allByStage((int) $stage['id']);
        }

        Response::view('admin/pipeline/board', [
            'title'   => 'Funil de vendas',
            'stages'  => $stages,
            'columns' => $columns,
        ], 'admin');
    }

    public function create(Request $request): void
    {
        $stages = (new PipelineStageModel())->allOrdered();

        if (empty($stages)) {
            Session::flash('error', 'Cadastre ao menos uma etapa antes de criar leads.');
            Response::redirect('/admin/pipeline/stages/create');
        }

        Response::view('admin/pipeline/leads/form', [
            'title'   => 'Novo lead',
            'item'    => null,
            'stages'  => $stages,
            'history' => [],
            'error'   => Session::getFlash('error'),
            'old'     => Session::getFlash('old', ['stage_id' => $request->query('stage_id', '')]),
        ], 'admin');
    }

    public function store(Request $request): void
    {
        $this->persist($request, null);
    }

    public function edit(Request $request): void
    {
        $id   = (int) $request->param('id');
        $item = (new LeadModel())->find($id);

        if ($item === null) {
            Response::abort(404);
        }

        Response::view('admin/pipeline/leads/form', [
            'title'   => 'Editar lead',
            'item'    => $item,
            'stages'  => (new PipelineStageModel())->allOrdered(),
            'history' => (new LeadStageHistoryModel())->historyForLead($id),
            'error'   => Session::getFlash('error'),
            'old'     => Session::getFlash('old', []),
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $this->persist($request, (int) $request->param('id'));
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');
        (new LeadModel())->delete($id);

        Session::flash('success', 'Lead removido.');
        Response::redirect('/admin/pipeline');
    }

    /**
     * Endpoint AJAX chamado pelo drag-and-drop do quadro Kanban
     * (public/js/modules/pipeline.js). Move o lead para o final da
     * coluna de destino e registra no histórico.
     */
    public function move(Request $request): void
    {
        $id         = (int) $request->param('id');
        $newStageId = (int) $request->input('stage_id');

        $leadModel  = new LeadModel();
        $stageModel = new PipelineStageModel();

        if ($leadModel->find($id) === null) {
            Response::json(['message' => 'Lead não encontrado.'], 404);
        }

        if ($stageModel->find($newStageId) === null) {
            Response::json(['message' => 'Etapa inválida.'], 422);
        }

        $fromStageId = $leadModel->moveToStage($id, $newStageId);

        if ($fromStageId !== null && $fromStageId !== $newStageId) {
            (new LeadStageHistoryModel())->record($id, $fromStageId, $newStageId, Auth::id());

            if (Config::feature('audit_log')) {
                (new AuditLogModel())->log(Auth::id(), 'pipeline.lead_moved', 'leads', $id, [
                    'from_stage_id' => $fromStageId,
                    'to_stage_id'   => $newStageId,
                ], $request);
            }
        }

        // CsrfMiddleware já rotacionou o token nesta requisição — devolve o
        // token atual para o JS continuar arrastando cards sem precisar
        // recarregar a página (senão o próximo POST cairia em 419).
        Response::json([
            'status' => 'ok',
            'csrf'   => Session::csrfToken(),
        ]);
    }

    private function persist(Request $request, ?int $id): void
    {
        $data = $request->only(['name', 'company', 'email', 'phone', 'notes', 'stage_id']);

        $validator = new Validator($data, [
            'name'     => ['required', 'max:150'],
            'company'  => ['max:150'],
            'email'    => ['email', 'max:150'],
            'phone'    => ['max:30'],
            'stage_id' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Session::flash('old', $data);
            Response::redirect($id === null ? '/admin/pipeline/leads/create' : "/admin/pipeline/leads/{$id}/edit");
        }

        $newStageId = (int) $data['stage_id'];

        if ((new PipelineStageModel())->find($newStageId) === null) {
            Session::flash('error', 'Etapa selecionada é inválida.');
            Session::flash('old', $data);
            Response::redirect($id === null ? '/admin/pipeline/leads/create' : "/admin/pipeline/leads/{$id}/edit");
        }

        $payload = [
            'name'    => Validator::sanitize($data['name']),
            'company' => Validator::sanitize($data['company'] ?? ''),
            'email'   => strtolower(trim((string) ($data['email'] ?? ''))),
            'phone'   => Validator::sanitize($data['phone'] ?? ''),
            'notes'   => Validator::sanitize($data['notes'] ?? ''),
        ];

        $leadModel = new LeadModel();

        if ($id === null) {
            $payload['stage_id']   = $newStageId;
            $payload['sort_order'] = $leadModel->nextSortOrder($newStageId);

            $leadId = $leadModel->insert($payload);

            (new LeadStageHistoryModel())->record($leadId, null, $newStageId, Auth::id());

            Session::flash('success', 'Lead criado com sucesso.');
        } else {
            $current     = $leadModel->find($id);
            $fromStageId = $current !== null ? (int) $current['stage_id'] : null;

            if ($fromStageId !== null && $fromStageId !== $newStageId) {
                $payload['stage_id']   = $newStageId;
                $payload['sort_order'] = $leadModel->nextSortOrder($newStageId);
            }

            $leadModel->update($id, $payload);

            if ($fromStageId !== null && $fromStageId !== $newStageId) {
                (new LeadStageHistoryModel())->record($id, $fromStageId, $newStageId, Auth::id());
            }

            Session::flash('success', 'Lead atualizado com sucesso.');
        }

        Response::redirect('/admin/pipeline');
    }
}

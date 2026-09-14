<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\WebhookEventModel;
use App\Services\StripeService;
use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Painel de webhooks falhos com reprocessamento manual.
 * Rota: GET /admin/webhooks, POST /admin/webhooks/{id}/retry
 */
final class WebhookAdminController
{
    public function index(Request $request): void
    {
        $model  = new WebhookEventModel();
        $status = $request->query('status', 'failed');

        $pdo  = (new \App\Models\QueryBuilder('webhook_events'))->getPdo();
        $stmt = $pdo->prepare(
            "SELECT * FROM webhook_events WHERE status = :status ORDER BY id DESC LIMIT 50"
        );
        $stmt->execute(['status' => $status]);
        $events = $stmt->fetchAll();

        Response::view('admin/webhooks/index', [
            'title'   => 'Webhooks',
            'events'  => $events,
            'status'  => $status,
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ], 'admin');
    }

    public function retry(Request $request): void
    {
        $id    = (int) $request->param('id');
        $model = new WebhookEventModel();
        $event = $model->find($id);

        if ($event === null) {
            Response::abort(404);
        }

        $payload = json_decode((string) $event['payload'], true);

        if (!is_array($payload)) {
            Session::flash('error', "Payload inválido para o evento #{$id}.");
            Response::redirect('/admin/webhooks');
        }

        try {
            // Reutiliza o WebhookController para processar o evento
            $webhookController = new \App\Controllers\Billing\WebhookController();
            $webhookController->processPublic($payload);
            $model->markProcessed($id);
            Session::flash('success', "Evento #{$id} reprocessado com sucesso.");
        } catch (\Throwable $e) {
            $model->markFailed($id, $e->getMessage());
            Session::flash('error', "Falha ao reprocessar evento #{$id}: " . $e->getMessage());
        }

        Response::redirect('/admin/webhooks');
    }
}

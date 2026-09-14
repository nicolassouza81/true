<?php

declare(strict_types=1);

namespace App\Controllers\Billing;

use App\Models\PlanModel;
use App\Models\SubscriptionModel;
use App\Models\WebhookEventModel;
use App\Services\StripeService;
use Core\Request;
use Core\Response;

/**
 * Recebe webhooks do Stripe, valida assinatura, registra evento
 * (idempotente via webhook_events) e atualiza assinaturas.
 *
 * Configure no Stripe Dashboard: {APP_URL}/billing/webhook
 * Eventos recomendados: checkout.session.completed,
 * customer.subscription.updated, customer.subscription.deleted
 */
final class WebhookController
{
    public function handle(Request $request): void
    {
        $payload   = file_get_contents('php://input') ?: '';
        $signature = $request->header('Stripe-Signature') ?? '';

        $stripe = new StripeService();

        try {
            $event = $stripe->verifyWebhookSignature($payload, $signature);
        } catch (\RuntimeException $e) {
            Response::json(['error' => $e->getMessage()], 400);
        }

        $eventModel = new WebhookEventModel();

        if ($eventModel->alreadyProcessed('stripe', $event['id'])) {
            Response::json(['status' => 'already_processed']);
        }

        $recordId = $eventModel->record('stripe', $event['id'], $event['type'], $event);

        try {
            $this->processEvent($event);
            $eventModel->markProcessed($recordId);
        } catch (\Throwable $e) {
            $eventModel->markFailed($recordId, $e->getMessage());
            Response::json(['error' => 'processing_failed'], 500);
        }

        Response::json(['status' => 'ok']);
    }

    /**
     * Ponto de entrada público para reprocessamento via admin.
     *
     * @param array<string, mixed> $event
     */
    public function processPublic(array $event): void
    {
        $this->processEvent($event);
    }

    /**
     * @param array{id:string, type:string, data:array} $event
     */
    private function processEvent(array $event): void
    {
        $object = $event['data']['object'] ?? [];

        match ($event['type']) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($object),
            default => null, // Eventos não tratados são apenas registrados
        };
    }

    private function handleCheckoutCompleted(array $session): void
    {
        $userId = (int) ($session['subscription_data']['metadata']['user_id']
            ?? $session['metadata']['user_id']
            ?? 0);

        $planId = (int) ($session['subscription_data']['metadata']['plan_id']
            ?? $session['metadata']['plan_id']
            ?? 0);

        if ($userId === 0 || $planId === 0 || empty($session['subscription'])) {
            return;
        }

        $stripe = new StripeService();
        $subscription = $stripe->retrieveSubscription((string) $session['subscription']);

        $this->syncSubscription($userId, $planId, $subscription);
    }

    private function handleSubscriptionUpdated(array $subscription): void
    {
        $model = new SubscriptionModel();
        $existing = $model->findByStripeSubscriptionId($subscription['id']);

        if ($existing === null) {
            return; // Sem referência local; nada a fazer
        }

        $this->syncSubscription((int) $existing['user_id'], (int) $existing['plan_id'], $subscription);
    }

    private function handleSubscriptionDeleted(array $subscription): void
    {
        (new SubscriptionModel())->markCanceled($subscription['id']);
    }

    private function syncSubscription(int $userId, int $planId, array $stripeSubscription): void
    {
        // Se o price mudou (upgrade/downgrade), tenta resolver o plano correspondente
        $priceId = $stripeSubscription['items']['data'][0]['price']['id'] ?? null;

        if ($priceId !== null) {
            $plan = (new PlanModel())->findByStripePriceId($priceId);
            if ($plan !== null) {
                $planId = (int) $plan['id'];
            }
        }

        (new SubscriptionModel())->upsertFromStripe($userId, $planId, [
            'stripe_customer_id'     => $stripeSubscription['customer'] ?? null,
            'stripe_subscription_id' => $stripeSubscription['id'],
            'status'                 => $stripeSubscription['status'],
            'current_period_start'   => isset($stripeSubscription['current_period_start'])
                ? date('Y-m-d H:i:s', (int) $stripeSubscription['current_period_start'])
                : null,
            'current_period_end'     => isset($stripeSubscription['current_period_end'])
                ? date('Y-m-d H:i:s', (int) $stripeSubscription['current_period_end'])
                : null,
            'cancel_at_period_end'   => !empty($stripeSubscription['cancel_at_period_end']) ? 1 : 0,
            'canceled_at'            => isset($stripeSubscription['canceled_at'])
                ? date('Y-m-d H:i:s', (int) $stripeSubscription['canceled_at'])
                : null,
        ]);
    }
}

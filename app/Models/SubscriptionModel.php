<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Model de assinaturas (subscriptions). Vincula usuário, plano e
 * referências externas do Stripe (customer/subscription IDs).
 */
final class SubscriptionModel extends QueryBuilder
{
    private const ACTIVE_STATUSES = ['trialing', 'active'];

    public function __construct()
    {
        parent::__construct('subscriptions');
    }

    public function findByUser(int $userId): ?array
    {
        return $this->findBy('user_id', $userId);
    }

    public function findByStripeSubscriptionId(string $stripeSubscriptionId): ?array
    {
        return $this->findBy('stripe_subscription_id', $stripeSubscriptionId);
    }

    public function findByStripeCustomerId(string $stripeCustomerId): ?array
    {
        return $this->findBy('stripe_customer_id', $stripeCustomerId);
    }

    public function isActive(array $subscription): bool
    {
        return in_array($subscription['status'], self::ACTIVE_STATUSES, true);
    }

    public function userHasActiveSubscription(int $userId): bool
    {
        $subscription = $this->findByUser($userId);

        return $subscription !== null && $this->isActive($subscription);
    }

    /**
     * Cria ou atualiza a assinatura de um usuário com base no
     * Stripe Subscription ID (upsert simples).
     */
    public function upsertFromStripe(int $userId, int $planId, array $stripeData): int
    {
        $existing = $this->findByStripeSubscriptionId($stripeData['stripe_subscription_id']);

        $payload = [
            'user_id'                => $userId,
            'plan_id'                => $planId,
            'stripe_customer_id'     => $stripeData['stripe_customer_id'] ?? null,
            'stripe_subscription_id' => $stripeData['stripe_subscription_id'],
            'status'                 => $stripeData['status'],
            'current_period_start'   => $stripeData['current_period_start'] ?? null,
            'current_period_end'     => $stripeData['current_period_end'] ?? null,
            'cancel_at_period_end'   => $stripeData['cancel_at_period_end'] ?? 0,
            'canceled_at'            => $stripeData['canceled_at'] ?? null,
        ];

        if ($existing !== null) {
            $this->update((int) $existing['id'], $payload);
            return (int) $existing['id'];
        }

        return $this->insert($payload);
    }

    public function markCanceled(string $stripeSubscriptionId): bool
    {
        $subscription = $this->findByStripeSubscriptionId($stripeSubscriptionId);

        if ($subscription === null) {
            return false;
        }

        return $this->update((int) $subscription['id'], [
            'status'      => 'canceled',
            'canceled_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

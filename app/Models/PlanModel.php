<?php

declare(strict_types=1);

namespace App\Models;

final class PlanModel extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct('plans');
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    public function findByStripePriceId(string $stripePriceId): ?array
    {
        return $this->findBy('stripe_price_id', $stripePriceId);
    }

    public function active(): array
    {
        return $this->all(['is_active' => 1], 'price_cents', 'ASC');
    }

    /** Decodifica o campo JSON `features`. */
    public function features(array $plan): array
    {
        if (empty($plan['features'])) {
            return [];
        }

        $decoded = json_decode((string) $plan['features'], true);

        return is_array($decoded) ? $decoded : [];
    }

    public function formattedPrice(array $plan): string
    {
        $value = ((int) $plan['price_cents']) / 100;
        $currency = $plan['currency'] ?? 'BRL';

        return match ($currency) {
            'BRL' => 'R$ ' . number_format($value, 2, ',', '.'),
            'USD' => '$' . number_format($value, 2, '.', ','),
            default => number_format($value, 2) . ' ' . $currency,
        };
    }
}

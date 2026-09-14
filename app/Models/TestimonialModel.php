<?php

declare(strict_types=1);

namespace App\Models;

final class TestimonialModel extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct('testimonials');
    }

    /** Retorna testemunhos ativos ordenados por sort_order. */
    public function active(): array
    {
        return $this->all(['is_active' => 1], 'sort_order', 'ASC');
    }

    /** Retorna N testemunhos ativos para exibição na home. */
    public function featured(int $limit = 6): array
    {
        return $this->all(['is_active' => 1], 'sort_order', 'ASC', $limit);
    }

    /** Stars como array para facilitar renderização (ex: foreach). */
    public function stars(array $testimonial): array
    {
        $rating = max(1, min(5, (int) ($testimonial['rating'] ?? 5)));
        return [
            'filled' => $rating,
            'empty'  => 5 - $rating,
        ];
    }
}

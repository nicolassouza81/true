<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Etapas do funil de vendas (colunas do Kanban).
 */
final class PipelineStageModel extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct('pipeline_stages');
    }

    /** @return array<int, array<string, mixed>> */
    public function allOrdered(): array
    {
        return $this->all([], 'sort_order', 'ASC');
    }

    /** Alias para allOrdered() */
    public function ordered(): array
    {
        return $this->allOrdered();
    }

    /** Próxima posição livre para uma nova etapa (adiciona ao final). */
    public function nextSortOrder(): int
    {
        return $this->count();
    }
}

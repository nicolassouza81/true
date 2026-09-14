<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Cards do funil (leads/oportunidades comerciais).
 */
final class LeadModel extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct('leads');
    }

    /** @return array<int, array<string, mixed>> */
    public function allByStage(int $stageId): array
    {
        return $this->all(['stage_id' => $stageId], 'sort_order', 'ASC');
    }

    /** Próxima posição livre dentro de uma etapa (adiciona ao final da coluna). */
    public function nextSortOrder(int $stageId): int
    {
        return $this->count(['stage_id' => $stageId]);
    }

    /**
     * Move um lead para outra etapa, colocando-o ao final da coluna de destino.
     * Retorna o stage_id anterior (útil para registrar o histórico), ou null
     * se o lead não existir.
     */
    public function moveToStage(int $leadId, int $newStageId): ?int
    {
        $lead = $this->find($leadId);

        if ($lead === null) {
            return null;
        }

        $previousStageId = (int) $lead['stage_id'];

        $this->update($leadId, [
            'stage_id'   => $newStageId,
            'sort_order' => $this->nextSortOrder($newStageId),
        ]);

        return $previousStageId;
    }
}

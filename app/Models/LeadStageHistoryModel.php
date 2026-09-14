<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Histórico de movimentação de leads entre etapas do funil.
 */
final class LeadStageHistoryModel extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct('lead_stage_history');
    }

    public function record(int $leadId, ?int $fromStageId, int $toStageId, ?int $movedBy): int
    {
        return $this->insert([
            'lead_id'       => $leadId,
            'from_stage_id' => $fromStageId,
            'to_stage_id'   => $toStageId,
            'moved_by'      => $movedBy,
        ]);
    }

    /**
     * Histórico de um lead, já com nomes das etapas e do usuário responsável
     * pela movimentação (join — não é o que o QueryBuilder genérico cobre).
     *
     * @return array<int, array<string, mixed>>
     */
    public function historyForLead(int $leadId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                h.id,
                h.moved_at,
                from_stage.name AS from_stage_name,
                to_stage.name   AS to_stage_name,
                u.name          AS moved_by_name
             FROM lead_stage_history h
             LEFT JOIN pipeline_stages from_stage ON from_stage.id = h.from_stage_id
             JOIN pipeline_stages to_stage ON to_stage.id = h.to_stage_id
             LEFT JOIN users u ON u.id = h.moved_by
             WHERE h.lead_id = :lead_id
             ORDER BY h.moved_at DESC, h.id DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':lead_id', $leadId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

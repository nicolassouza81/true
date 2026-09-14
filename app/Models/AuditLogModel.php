<?php

declare(strict_types=1);

namespace App\Models;

use Core\Request;

final class AuditLogModel extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct('audit_logs');
    }

    /**
     * Registra uma ação de auditoria.
     *
     * @param array<string, mixed>|null $metadata
     */
    public function log(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, ?array $metadata = null, ?Request $request = null): int
    {
        return $this->insert([
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'metadata'    => $metadata !== null ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            'ip_address'  => $request?->ip(),
            'user_agent'  => $request?->userAgent(),
        ]);
    }

    public function recentForUser(int $userId, int $limit = 20): array
    {
        return $this->all(['user_id' => $userId], 'id', 'DESC', $limit);
    }
}

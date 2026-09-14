<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Armazena eventos de webhook (Stripe) para idempotência e
 * reprocessamento em caso de falha.
 */
final class WebhookEventModel extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct('webhook_events');
    }

    public function findByEventId(string $provider, string $eventId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM webhook_events WHERE provider = :provider AND event_id = :event_id LIMIT 1'
        );
        $stmt->execute(['provider' => $provider, 'event_id' => $eventId]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function alreadyProcessed(string $provider, string $eventId): bool
    {
        $event = $this->findByEventId($provider, $eventId);

        return $event !== null && $event['status'] === 'processed';
    }

    /**
     * Registra um evento recebido (idempotente: ignora duplicados).
     *
     * @param array<string, mixed> $payload
     */
    public function record(string $provider, string $eventId, string $eventType, array $payload): int
    {
        $existing = $this->findByEventId($provider, $eventId);

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        return $this->insert([
            'provider'   => $provider,
            'event_id'   => $eventId,
            'event_type' => $eventType,
            'payload'    => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status'     => 'pending',
        ]);
    }

    public function markProcessed(int $id): bool
    {
        return $this->update($id, [
            'status'       => 'processed',
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markFailed(int $id, string $error): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE webhook_events
             SET status = "failed", attempts = attempts + 1, last_error = :error
             WHERE id = :id'
        );

        return $stmt->execute(['error' => $error, 'id' => $id]);
    }

    public function pending(int $limit = 50): array
    {
        return $this->all(['status' => 'pending'], 'id', 'ASC', $limit);
    }
}

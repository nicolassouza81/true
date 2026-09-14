<?php

declare(strict_types=1);

namespace Core;

use PDO;

/**
 * Controle de tentativas (login, reset de senha, etc.) baseado na
 * tabela `rate_limits`. Identificador sugerido: IP ou IP+email.
 */
final class RateLimiter
{
    private PDO $pdo;
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(?int $maxAttempts = null, ?int $decayMinutes = null)
    {
        $this->pdo = Database::connection();
        $this->maxAttempts = $maxAttempts ?? Env::getInt('RATE_LIMIT_MAX_ATTEMPTS', 5);
        $this->decayMinutes = $decayMinutes ?? Env::getInt('RATE_LIMIT_DECAY_MINUTES', 15);
    }

    /**
     * Verifica se o identificador está bloqueado para a ação informada.
     */
    public function tooManyAttempts(string $identifier, string $action): bool
    {
        $row = $this->find($identifier, $action);

        if ($row === null) {
            return false;
        }

        if ($row['blocked_until'] !== null && strtotime($row['blocked_until']) > time()) {
            return true;
        }

        return false;
    }

    /**
     * Registra uma tentativa. Se exceder o limite, define `blocked_until`.
     */
    public function hit(string $identifier, string $action): void
    {
        $row = $this->find($identifier, $action);
        $now = date('Y-m-d H:i:s');

        if ($row === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO rate_limits (identifier, action, attempts, first_attempt_at, last_attempt_at)
                 VALUES (:identifier, :action, 1, :now, :now)'
            );
            $stmt->execute(['identifier' => $identifier, 'action' => $action, 'now' => $now]);
            return;
        }

        $attempts = (int) $row['attempts'] + 1;
        $blockedUntil = null;

        if ($attempts >= $this->maxAttempts) {
            $blockedUntil = date('Y-m-d H:i:s', time() + ($this->decayMinutes * 60));
        }

        $stmt = $this->pdo->prepare(
            'UPDATE rate_limits
             SET attempts = :attempts, last_attempt_at = :now, blocked_until = :blocked_until
             WHERE identifier = :identifier AND action = :action'
        );

        $stmt->execute([
            'attempts'      => $attempts,
            'now'           => $now,
            'blocked_until' => $blockedUntil,
            'identifier'    => $identifier,
            'action'        => $action,
        ]);
    }

    /** Limpa o contador (ex: após login bem-sucedido). */
    public function clear(string $identifier, string $action): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM rate_limits WHERE identifier = :identifier AND action = :action'
        );
        $stmt->execute(['identifier' => $identifier, 'action' => $action]);
    }

    /** Segundos restantes até desbloquear. */
    public function availableInSeconds(string $identifier, string $action): int
    {
        $row = $this->find($identifier, $action);

        if ($row === null || $row['blocked_until'] === null) {
            return 0;
        }

        $remaining = strtotime($row['blocked_until']) - time();

        return max(0, $remaining);
    }

    private function find(string $identifier, string $action): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM rate_limits WHERE identifier = :identifier AND action = :action LIMIT 1'
        );
        $stmt->execute(['identifier' => $identifier, 'action' => $action]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}

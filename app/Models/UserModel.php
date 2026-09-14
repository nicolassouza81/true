<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Model de usuários. Senhas devem ser hashed antes de chegar aqui
 * (use UserModel::hashPassword()).
 */
final class UserModel extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct('users');
    }

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    /**
     * Cria um novo usuário com senha já hasheada.
     *
     * @param array{name:string,email:string,password:string,role_id?:int} $data
     */
    public function createUser(array $data): int
    {
        return $this->insert([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => self::hashPassword($data['password']),
            'role_id'  => $data['role_id'] ?? 2,
            'status'   => 'active',
        ]);
    }

    public function updatePassword(int $userId, string $newPlainPassword): bool
    {
        // Busca versão atual via prepared statement (sem interpolação direta)
        $stmt = $this->pdo->prepare(
            'SELECT session_version FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $current = (int) ($stmt->fetchColumn() ?: 1);

        return $this->update($userId, [
            'password'        => self::hashPassword($newPlainPassword),
            'session_version' => $current + 1,
        ]);
    }

    /** Incrementa session_version, invalidando todas as sessões ativas do usuário. */
    public function invalidateSessions(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET session_version = session_version + 1 WHERE id = :id'
        );
        return $stmt->execute(['id' => $userId]);
    }

    /** Retorna usuário sem o campo password. */
    public function findSafe(int $id): ?array
    {
        $user = $this->find($id);

        if ($user === null) {
            return null;
        }

        unset($user['password']);

        return $user;
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);

        return [
            'data'  => $this->all([], 'id', 'DESC', $perPage, $offset),
            'total' => $this->count(),
            'page'  => $page,
            'pages' => (int) ceil($this->count() / $perPage),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

final class RoleModel extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct('roles');
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    /** Decodifica o campo JSON `permissions`. */
    public function permissionsFor(int $roleId): array
    {
        $role = $this->find($roleId);

        if ($role === null || empty($role['permissions'])) {
            return [];
        }

        $decoded = json_decode((string) $role['permissions'], true);

        return is_array($decoded) ? $decoded : [];
    }

    public function hasPermission(int $roleId, string $permission): bool
    {
        $permissions = $this->permissionsFor($roleId);

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }
}

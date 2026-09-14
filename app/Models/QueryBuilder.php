<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use InvalidArgumentException;
use PDO;

/**
 * Query Builder simples sobre PDO com prepared statements.
 * Não substitui SQL puro para queries complexas — use $this->pdo->prepare()
 * diretamente nos models quando necessário.
 */
class QueryBuilder
{
    protected PDO $pdo;
    protected string $table;

    public function __construct(string $table)
    {
        $this->pdo   = Database::connection();
        $this->table = $table;
    }

    /**
     * Garante que um identificador (nome de coluna) usado por interpolação
     * direta na SQL é seguro — PDO não tem como parametrizar nomes de
     * coluna/tabela, então tudo que chega aqui vindo (direta ou
     * indiretamente) de input do usuário precisa passar por esta whitelist
     * antes de entrar na query.
     */
    private static function assertSafeIdentifier(string $name): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new InvalidArgumentException("Nome de coluna inválido: {$name}");
        }

        return $name;
    }

    /** @return array<string, mixed>|null */
    public function find(int|string $id, string $pk = 'id'): ?array
    {
        $pk = self::assertSafeIdentifier($pk);

        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE {$pk} = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @return array<string, mixed>|null */
    public function findBy(string $column, mixed $value): ?array
    {
        $column = self::assertSafeIdentifier($column);

        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1"
        );
        $stmt->execute(['value' => $value]);

        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Retorna múltiplos registros com filtros opcionais.
     *
     * @param  array<string, mixed> $conditions Pares coluna => valor (AND)
     * @return array<int, array<string, mixed>>
     */
    public function all(
        array   $conditions = [],
        string  $orderBy    = 'id',
        string  $direction  = 'ASC',
        ?int    $limit      = null,
        int     $offset     = 0
    ): array {
        $sql    = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $column         = self::assertSafeIdentifier((string) $column);
                $param          = 'w_' . $column;
                $where[]        = "{$column} = :{$param}";
                $params[$param] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $orderBy   = self::assertSafeIdentifier($orderBy);
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql      .= " ORDER BY {$orderBy} {$direction}";

        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** @param array<string, mixed> $conditions */
    public function count(array $conditions = []): int
    {
        $sql    = "SELECT COUNT(*) AS total FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $column         = self::assertSafeIdentifier((string) $column);
                $param          = 'w_' . $column;
                $where[]        = "{$column} = :{$param}";
                $params[$param] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Insere um registro e retorna o ID gerado.
     *
     * @param array<string, mixed> $data
     */
    public function insert(array $data): int
    {
        $columns      = array_map(static fn ($c) => self::assertSafeIdentifier((string) $c), array_keys($data));
        $placeholders = array_map(static fn ($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->pdo->prepare($sql)->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Atualiza um registro pelo ID.
     * Usa prefixo interno '__pk' para o parâmetro da chave primária,
     * evitando colisão com colunas de mesmo nome em $data.
     *
     * @param array<string, mixed> $data
     */
    public function update(int|string $id, array $data, string $pk = 'id'): bool
    {
        if (empty($data)) {
            return false;
        }

        $pk  = self::assertSafeIdentifier($pk);
        $set = [];
        foreach (array_keys($data) as $column) {
            $column = self::assertSafeIdentifier((string) $column);
            $set[]  = "{$column} = :{$column}";
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :__pk',
            $this->table,
            implode(', ', $set),
            $pk
        );

        // '__pk' não colide com nomes de coluna reais (prefixo reservado)
        $params         = $data;
        $params['__pk'] = $id;

        return $this->pdo->prepare($sql)->execute($params);
    }

    public function delete(int|string $id, string $pk = 'id'): bool
    {
        $pk = self::assertSafeIdentifier($pk);

        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE {$pk} = :id"
        );

        return $stmt->execute(['id' => $id]);
    }

    /** Expõe a conexão PDO para queries customizadas em subclasses. */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
}

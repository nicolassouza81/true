<?php

declare(strict_types=1);

/**
 * Migration runner — executa os arquivos .sql em database/migrations
 * em ordem alfabética, registrando os já executados na tabela `migrations`.
 *
 * Uso: php database/migrate.php
 */

$root = dirname(__DIR__);

$envFile = $root . '/.env';
$env = [];

if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$db   = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

if ($db === '') {
    fwrite(STDERR, "Erro: DB_DATABASE não definido no .env\n");
    exit(1);
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    fwrite(STDERR, 'Erro ao conectar no banco: ' . $e->getMessage() . "\n");
    exit(1);
}

// Tabela de controle de migrations
$pdo->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(180) NOT NULL UNIQUE,
        executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$stmt = $pdo->query('SELECT migration FROM migrations');
$executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files);

if (empty($files)) {
    echo "Nenhum arquivo de migration encontrado em {$migrationsDir}\n";
    exit(0);
}

$ranAny = false;

foreach ($files as $file) {
    $name = basename($file);

    if (in_array($name, $executed, true)) {
        continue;
    }

    $sql = file_get_contents($file);

    if ($sql === false || trim($sql) === '') {
        fwrite(STDERR, "Aviso: arquivo vazio ou ilegível: {$name}\n");
        continue;
    }

    echo "Executando: {$name} ... ";

    try {
        $pdo->beginTransaction();

        // Permite múltiplos statements separados por ';'
        foreach (splitSqlStatements($sql) as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }
            $pdo->exec($statement);
        }

        $insert = $pdo->prepare('INSERT INTO migrations (migration) VALUES (:migration)');
        $insert->execute(['migration' => $name]);

        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
        $ranAny = true;
        echo "OK\n";
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, "ERRO\n");
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        exit(1);
    }
}

if (!$ranAny) {
    echo "Nada para executar. Banco já está atualizado.\n";
} else {
    echo "Migrations concluídas com sucesso.\n";
}

/**
 * Divide um arquivo SQL em statements individuais, respeitando
 * blocos simples (não lida com DELIMITER customizado / procedures).
 *
 * @return string[]
 */
function splitSqlStatements(string $sql): array
{
    // Remove comentários de linha simples começando com --
    $lines = explode("\n", $sql);
    $clean = [];

    foreach ($lines as $line) {
        $trimmed = ltrim($line);
        if (str_starts_with($trimmed, '--')) {
            continue;
        }
        $clean[] = $line;
    }

    $sql = implode("\n", $clean);

    return array_filter(
        array_map('trim', explode(';', $sql)),
        fn ($stmt) => $stmt !== ''
    );
}

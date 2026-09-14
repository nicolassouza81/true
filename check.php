#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * check.php — Verifica se o ambiente está pronto para rodar a aplicação.
 * Uso: php check.php
 *
 * Testa: versão PHP, extensões, permissões de diretório,
 * conectividade com o banco e variáveis de ambiente obrigatórias.
 */

$errors   = [];
$warnings = [];
$ok       = [];

// ----------------------------------------------------------------
// 1. Versão PHP
// ----------------------------------------------------------------
if (PHP_VERSION_ID < 80100) {
    $errors[] = 'PHP 8.1+ obrigatório. Versão atual: ' . PHP_VERSION;
} else {
    $ok[] = 'PHP ' . PHP_VERSION;
}

// ----------------------------------------------------------------
// 2. Extensões obrigatórias
// ----------------------------------------------------------------
$required = ['pdo', 'pdo_mysql', 'mbstring', 'curl', 'json', 'openssl', 'session'];

foreach ($required as $ext) {
    if (!extension_loaded($ext)) {
        $errors[] = "Extensão ausente: {$ext}";
    } else {
        $ok[] = "ext/{$ext}";
    }
}

// ----------------------------------------------------------------
// 3. Arquivo .env
// ----------------------------------------------------------------
$root   = __DIR__;
$envFile = $root . '/.env';

if (!is_file($envFile)) {
    $errors[] = '.env não encontrado. Copie .env.example para .env e preencha.';
} else {
    $ok[] = '.env encontrado';

    // Parse básico
    $env = [];
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $env[trim($k)] = trim($v, " \t\"'");
    }

    // Variáveis obrigatórias
    $requiredEnv = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'APP_URL'];
    foreach ($requiredEnv as $key) {
        if (empty($env[$key])) {
            $errors[] = ".env: {$key} não definido ou vazio.";
        } else {
            $ok[] = ".env: {$key} = {$env[$key]}";
        }
    }

    // Verifica configuração de e-mail
    $mailDriver = $env['MAIL_DRIVER'] ?? 'native';

    if ($mailDriver === 'mailgun') {
        if (empty($env['MAILGUN_API_KEY'])) {
            $errors[] = '.env: MAILGUN_API_KEY não definido (necessário para MAIL_DRIVER=mailgun).';
        }
        if (empty($env['MAILGUN_DOMAIN'])) {
            $errors[] = '.env: MAILGUN_DOMAIN não definido (necessário para MAIL_DRIVER=mailgun).';
        }
        if (!empty($env['MAILGUN_API_KEY']) && !empty($env['MAILGUN_DOMAIN'])) {
            $ok[] = "Mailgun: domínio {$env['MAILGUN_DOMAIN']} configurado";
        }
    } else {
        $warnings[] = "MAIL_DRIVER={$mailDriver} (mail() nativo). Recomendado usar 'mailgun' em produção.";
    }

    // Variáveis recomendadas (não bloqueiam o setup, mas billing/sessão
    // dependem delas em produção)
    $recommended = [
        'STRIPE_PUBLIC_KEY',
        'STRIPE_SECRET_KEY',
        'STRIPE_WEBHOOK_SECRET',
        'SESSION_NAME',
    ];
    foreach ($recommended as $key) {
        if (empty($env[$key])) {
            $warnings[] = ".env: {$key} não definido (necessário para billing/sessão).";
        }
    }

    // ----------------------------------------------------------------
    // 3b. Checagens específicas de produção
    // ----------------------------------------------------------------
    $appEnv   = $env['APP_ENV'] ?? 'local';
    $appDebug = strtolower((string) ($env['APP_DEBUG'] ?? 'false'));

    if ($appEnv === 'production') {
        if (in_array($appDebug, ['true', '1', '(true)'], true)) {
            $errors[] = '.env: APP_DEBUG=true em produção. Isso vaza stack traces para o visitante — defina APP_DEBUG=false.';
        } else {
            $ok[] = 'APP_DEBUG=false em produção';
        }

        $htaccessFile = $root . '/.htaccess';
        if (is_file($htaccessFile) && str_contains(file_get_contents($htaccessFile) ?: '', '# RewriteCond %{HTTPS} off')) {
            $warnings[] = '.htaccess: redirecionamento para HTTPS está comentado. Descomente antes de ir ao ar em produção.';
        }
    }

    // ----------------------------------------------------------------
    // 4. Conectividade com o banco
    // ----------------------------------------------------------------
    if (!empty($env['DB_DATABASE'])) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $env['DB_HOST'] ?? '127.0.0.1',
            $env['DB_PORT'] ?? '3306',
            $env['DB_DATABASE']
        );

        try {
            $pdo = new PDO($dsn, $env['DB_USERNAME'] ?? '', $env['DB_PASSWORD'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $ok[] = "Banco de dados: conexão OK ({$env['DB_DATABASE']})";

            // Verifica se migrations foram rodadas
            try {
                $tables = $pdo->query("SHOW TABLES LIKE 'migrations'")->fetchColumn();
                if (!$tables) {
                    $warnings[] = 'Tabela migrations não encontrada. Execute: php database/migrate.php';
                } else {
                    $count = (int) $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn();
                    $ok[]  = "Migrations: {$count} executada(s)";
                }
            } catch (PDOException $e) {
                $warnings[] = 'Não foi possível verificar migrations: ' . $e->getMessage();
            }
        } catch (PDOException $e) {
            $errors[] = 'Banco de dados: falha na conexão — ' . $e->getMessage();
        }
    }
}

// ----------------------------------------------------------------
// 5. Permissões de diretório
// ----------------------------------------------------------------
$writableDirs = [
    'storage/cache' => $root . '/storage/cache',
    'storage/logs'  => $root . '/storage/logs',
];

foreach ($writableDirs as $label => $path) {
    if (!is_dir($path)) {
        if (!mkdir($path, 0775, true)) {
            $errors[] = "Diretório não encontrado e não pôde ser criado: {$label}";
            continue;
        }
    }

    if (!is_writable($path)) {
        $errors[] = "Sem permissão de escrita em: {$label} — execute: chmod -R 775 {$label}";
    } else {
        $ok[] = "Permissão de escrita: {$label}";
    }
}

// ----------------------------------------------------------------
// 6. mod_rewrite (apenas Apache)
// ----------------------------------------------------------------
if (function_exists('apache_get_modules')) {
    if (in_array('mod_rewrite', apache_get_modules(), true)) {
        $ok[] = 'Apache mod_rewrite: ativo';
    } else {
        $errors[] = 'Apache mod_rewrite não está ativo. Necessário para o roteamento funcionar.';
    }
} else {
    $warnings[] = 'Não foi possível verificar mod_rewrite (não é Apache ou CLI). Verifique manualmente.';
}

// ----------------------------------------------------------------
// Output
// ----------------------------------------------------------------
$isCI = (bool) getenv('CI');
$nl   = PHP_EOL;

if (!$isCI) {
    echo $nl;
    echo '╔════════════════════════════════════════╗' . $nl;
    echo '║        VERIFICAÇÃO DE AMBIENTE         ║' . $nl;
    echo '╚════════════════════════════════════════╝' . $nl . $nl;
}

foreach ($ok as $msg) {
    echo "  ✓  {$msg}{$nl}";
}

if (!empty($warnings)) {
    echo $nl;
    foreach ($warnings as $msg) {
        echo "  ⚠  {$msg}{$nl}";
    }
}

if (!empty($errors)) {
    echo $nl;
    foreach ($errors as $msg) {
        echo "  ✗  {$msg}{$nl}";
    }
    echo $nl . '  Corrija os erros acima antes de prosseguir.' . $nl . $nl;
    exit(1);
}

echo $nl . '  Ambiente OK. Pronto para uso.' . $nl . $nl;
exit(0);

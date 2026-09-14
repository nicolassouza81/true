#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Seed de dados iniciais para desenvolvimento e testes.
 *
 * Cria:
 *   - 1 usuário admin
 *   - 1 plano de exemplo
 *   - 3 testemunhos de exemplo
 *   - 3 logos de empresas de exemplo
 *
 * Uso: php database/seeds/seed.php
 *
 * ATENÇÃO: Execute apenas em ambiente local/staging. Não rodar em produção.
 */

$root = dirname(__DIR__, 2);
require $root . '/core/Autoloader.php';
\Core\Env::load($root . '/.env');

$pdo = \Core\Database::connection();

echo PHP_EOL . 'Executando seeds...' . PHP_EOL . PHP_EOL;

// ----------------------------------------------------------------
// Admin
// ----------------------------------------------------------------
$existing = $pdo->prepare('SELECT id FROM users WHERE email = :email');
$existing->execute(['email' => 'admin@example.com']);

if (!$existing->fetch()) {
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password, role_id, status)
         VALUES (:name, :email, :password, 1, "active")'
    );
    $stmt->execute([
        'name'     => 'Administrador',
        'email'    => 'admin@example.com',
        'password' => password_hash('password', PASSWORD_BCRYPT),
    ]);
    echo '  ✓ Admin criado: admin@example.com / password' . PHP_EOL;
} else {
    echo '  – Admin já existe, pulando.' . PHP_EOL;
}

// ----------------------------------------------------------------
// Plano de exemplo
// ----------------------------------------------------------------
$existingPlan = $pdo->prepare('SELECT id FROM plans WHERE slug = :slug');
$existingPlan->execute(['slug' => 'pro-mensal']);

if (!$existingPlan->fetch()) {
    $stmt = $pdo->prepare(
        'INSERT INTO plans (slug, name, description, price_cents, currency, interval_unit, features, is_active)
         VALUES (:slug, :name, :description, :price_cents, "BRL", "month", :features, 1)'
    );
    $stmt->execute([
        'slug'        => 'pro-mensal',
        'name'        => 'Pro',
        'description' => 'Acesso completo a todos os recursos.',
        'price_cents' => 9900,
        'features'    => json_encode(['Acesso ilimitado', 'Suporte prioritário', 'Relatórios avançados']),
    ]);
    echo '  ✓ Plano Pro criado (R$ 99,00/mês)' . PHP_EOL;
} else {
    echo '  – Plano já existe, pulando.' . PHP_EOL;
}

// ----------------------------------------------------------------
// Testemunhos
// ----------------------------------------------------------------
$testimonials = [
    [
        'author_name'  => 'Mariana Costa',
        'author_title' => 'CEO, TechBrasil',
        'author_avatar'=> '',
        'content'      => 'Excelente plataforma! Reduziu nosso tempo de onboarding em 60%. Recomendo a qualquer empresa que busque eficiência.',
        'rating'       => 5,
        'sort_order'   => 1,
    ],
    [
        'author_name'  => 'Felipe Andrade',
        'author_title' => 'CTO, Startupz',
        'author_avatar'=> '',
        'content'      => 'A integração com Stripe funcionou perfeitamente desde o primeiro dia. Suporte técnico ágil e equipe atenciosa.',
        'rating'       => 5,
        'sort_order'   => 2,
    ],
    [
        'author_name'  => 'Renata Oliveira',
        'author_title' => 'Gerente de Produto, Inovare',
        'author_avatar'=> '',
        'content'      => 'Melhor custo-benefício que encontrei no mercado. A equipe de suporte resolve qualquer dúvida rapidamente.',
        'rating'       => 4,
        'sort_order'   => 3,
    ],
];

$existingTestimonial = $pdo->query('SELECT COUNT(*) FROM testimonials')->fetchColumn();

if ((int) $existingTestimonial === 0) {
    $stmt = $pdo->prepare(
        'INSERT INTO testimonials (author_name, author_title, author_avatar, content, rating, is_active, sort_order)
         VALUES (:author_name, :author_title, :author_avatar, :content, :rating, 1, :sort_order)'
    );
    foreach ($testimonials as $t) {
        $stmt->execute($t);
    }
    echo '  ✓ ' . count($testimonials) . ' testemunhos criados' . PHP_EOL;
} else {
    echo '  – Testemunhos já existem, pulando.' . PHP_EOL;
}

// ----------------------------------------------------------------
// Logos de empresas
// ----------------------------------------------------------------
$logos = [
    [
        'name'        => 'TechBrasil',
        'logo_url'    => 'https://placehold.co/160x48/e2e6ea/6c757d?text=TechBrasil',
        'website_url' => 'https://example.com',
        'alt_text'    => 'Logo TechBrasil',
        'sort_order'  => 1,
    ],
    [
        'name'        => 'Startupz',
        'logo_url'    => 'https://placehold.co/160x48/e2e6ea/6c757d?text=Startupz',
        'website_url' => 'https://example.com',
        'alt_text'    => 'Logo Startupz',
        'sort_order'  => 2,
    ],
    [
        'name'        => 'Inovare',
        'logo_url'    => 'https://placehold.co/160x48/e2e6ea/6c757d?text=Inovare',
        'website_url' => 'https://example.com',
        'alt_text'    => 'Logo Inovare',
        'sort_order'  => 3,
    ],
];

$existingLogos = $pdo->query('SELECT COUNT(*) FROM company_logos')->fetchColumn();

if ((int) $existingLogos === 0) {
    $stmt = $pdo->prepare(
        'INSERT INTO company_logos (name, logo_url, website_url, alt_text, is_active, sort_order)
         VALUES (:name, :logo_url, :website_url, :alt_text, 1, :sort_order)'
    );
    foreach ($logos as $logo) {
        $stmt->execute($logo);
    }
    echo '  ✓ ' . count($logos) . ' logos criados' . PHP_EOL;
} else {
    echo '  – Logos já existem, pulando.' . PHP_EOL;
}

// ----------------------------------------------------------------
// Funil de vendas — etapas padrão + leads de exemplo
// ----------------------------------------------------------------
$stages = [
    ['name' => 'Novo lead',  'color' => '#5b8def', 'sort_order' => 1, 'is_won' => 0, 'is_lost' => 0],
    ['name' => 'Contato',    'color' => '#f0a500', 'sort_order' => 2, 'is_won' => 0, 'is_lost' => 0],
    ['name' => 'Proposta',   'color' => '#17a2b8', 'sort_order' => 3, 'is_won' => 0, 'is_lost' => 0],
    ['name' => 'Ganho',      'color' => '#28a745', 'sort_order' => 4, 'is_won' => 1, 'is_lost' => 0],
    ['name' => 'Perdido',    'color' => '#dc3545', 'sort_order' => 5, 'is_won' => 0, 'is_lost' => 1],
];

$existingStages = $pdo->query('SELECT COUNT(*) FROM pipeline_stages')->fetchColumn();
$stageIds = [];

if ((int) $existingStages === 0) {
    $stmt = $pdo->prepare(
        'INSERT INTO pipeline_stages (name, color, sort_order, is_won, is_lost)
         VALUES (:name, :color, :sort_order, :is_won, :is_lost)'
    );
    foreach ($stages as $s) {
        $stmt->execute($s);
        $stageIds[$s['name']] = (int) $pdo->lastInsertId();
    }
    echo '  ✓ ' . count($stages) . ' etapas do funil criadas' . PHP_EOL;
} else {
    echo '  – Etapas do funil já existem, pulando.' . PHP_EOL;
    $rows = $pdo->query('SELECT id, name FROM pipeline_stages')->fetchAll();
    foreach ($rows as $row) {
        $stageIds[$row['name']] = (int) $row['id'];
    }
}

if (!empty($stageIds)) {
    $leads = [
        ['name' => 'João Pereira',  'company' => 'Comércio Silva',   'email' => 'joao@comerciosilva.com.br',  'phone' => '(11) 98888-1111', 'stage' => 'Novo lead'],
        ['name' => 'Ana Ribeiro',   'company' => 'Ribeiro Contábil', 'email' => 'ana@ribeirocontabil.com.br', 'phone' => '(11) 98888-2222', 'stage' => 'Contato'],
        ['name' => 'Carlos Souza',  'company' => 'Souza Materiais',  'email' => 'carlos@souzamateriais.com.br', 'phone' => '(11) 98888-3333', 'stage' => 'Proposta'],
    ];

    $existingLeads = $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();

    if ((int) $existingLeads === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO leads (stage_id, name, company, email, phone, sort_order)
             VALUES (:stage_id, :name, :company, :email, :phone, 0)'
        );
        $historyStmt = $pdo->prepare(
            'INSERT INTO lead_stage_history (lead_id, from_stage_id, to_stage_id)
             VALUES (:lead_id, NULL, :to_stage_id)'
        );

        foreach ($leads as $lead) {
            $stageId = $stageIds[$lead['stage']] ?? reset($stageIds);
            $stmt->execute([
                'stage_id' => $stageId,
                'name'     => $lead['name'],
                'company'  => $lead['company'],
                'email'    => $lead['email'],
                'phone'    => $lead['phone'],
            ]);
            $historyStmt->execute([
                'lead_id'      => (int) $pdo->lastInsertId(),
                'to_stage_id'  => $stageId,
            ]);
        }
        echo '  ✓ ' . count($leads) . ' leads de exemplo criados' . PHP_EOL;
    } else {
        echo '  – Leads já existem, pulando.' . PHP_EOL;
    }
}

echo PHP_EOL . '  Seeds concluídas.' . PHP_EOL . PHP_EOL;

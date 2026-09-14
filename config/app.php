<?php

declare(strict_types=1);

use Core\Env;

/**
 * Configuração geral da aplicação.
 * Edite este arquivo ao clonar o projeto para um novo cliente:
 * altere "name", "branding" e "features" conforme o caso.
 */
return [
    'name' => Env::get('APP_NAME', 'True Eventos'),
    'env'  => Env::get('APP_ENV', 'local'),
    'debug' => Env::getBool('APP_DEBUG', false),
    'url'  => rtrim((string) Env::get('APP_URL', 'http://localhost'), '/'),

    // Branding — usado em layouts/views para fácil customização por projeto
    'branding' => [
        'logo'           => '/public/assets/brand/true-horizontal-black.svg',
        'favicon'        => '/public/assets/brand/true-square-white-on-black.svg',
        'primary_color'  => '#080808',
        'accent_color'   => '#ff4b19',
    ],

    // Feature flags — ative/desative módulos sem remover código
    'features' => [
        'registration' => false,  // Cadastro externo desativado
        'billing'      => false,  // Módulo de planos/Stripe desativado
        'admin_panel'  => true,   // Painel administrativo
        'audit_log'    => true,   // Registro de auditoria
        'rate_limit'   => true,   // RateLimiter em login/reset
        'pipeline'     => true,   // Funil de vendas (Kanban) em /admin/pipeline
    ],

    // Locale / formatação
    'locale' => [
        'language' => 'pt-BR',
        'timezone' => 'America/Sao_Paulo',
        'currency' => 'BRL',
    ],

    // E-mail
    'mail' => [
        'driver'      => Env::get('MAIL_DRIVER', 'native'), // 'mailgun' | 'native'
        'from'        => Env::get('MAIL_FROM', 'noreply@localhost'),
        'from_name'   => Env::get('MAIL_FROM_NAME', 'App'),
    ],
];

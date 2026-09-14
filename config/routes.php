<?php

declare(strict_types=1);

use App\Controllers\Admin\CompanyLogoController;
use App\Controllers\Admin\CrudController;
use App\Controllers\Admin\PipelineController;
use App\Controllers\Admin\PipelineStageController;
use App\Controllers\Admin\TestimonialController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\WebhookAdminController;
use App\Controllers\AuthController;
use App\Controllers\Billing\SubscriptionController;
use App\Controllers\Billing\WebhookController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\OrcamentoController;
use App\Controllers\ProfileController;
use App\Controllers\SiteController;
use Core\Middleware\AdminMiddleware;
use Core\Middleware\AuthMiddleware;
use Core\Middleware\CsrfMiddleware;
use Core\Response;
use Core\Router;
use Core\Session;

/**
 * Definição central de rotas.
 * Convenção: handler como [Controller::class, 'method'], middlewares como array de FQCN.
 *
 * CSRF: aplicado em todas as rotas de escrita (POST/PUT/DELETE), exceto webhook Stripe
 * (que usa validação por assinatura própria).
 */
return function (Router $router): void {

    // ----------------------------------------------------------------
    // Público (Site Institucional TRUE Eventos)
    // ----------------------------------------------------------------
    $router->get('/', [SiteController::class, 'home']);
    $router->get('/index.html', [SiteController::class, 'home']);
    $router->get('/index.php', [SiteController::class, 'home']);
    $router->get('/sobre', [SiteController::class, 'about']);
    $router->get('/about', [SiteController::class, 'about']);
    $router->get('/about.html', [SiteController::class, 'about']);
    $router->get('/orcamento', [SiteController::class, 'orcamento']);
    $router->get('/orcamento.html', [SiteController::class, 'orcamento']);
    $router->get('/solucoes', function (): void { Response::redirect('/#solucoes'); });
    $router->get('/solucoes/{slug}', [SiteController::class, 'solucao']);
    $router->get('/solucoes/{slug}.html', [SiteController::class, 'solucao']);
    $router->get('/som', function (): void { Response::redirect('/solucoes/som.html'); });
    $router->get('/som.html', function (): void { Response::redirect('/solucoes/som.html'); });
    $router->get('/iluminacao', function (): void { Response::redirect('/solucoes/iluminacao.html'); });
    $router->get('/iluminacao.html', function (): void { Response::redirect('/solucoes/iluminacao.html'); });
    $router->get('/paineis-led', function (): void { Response::redirect('/solucoes/paineis-led.html'); });
    $router->get('/paineis-led.html', function (): void { Response::redirect('/solucoes/paineis-led.html'); });
    $router->get('/estruturas', function (): void { Response::redirect('/solucoes/estruturas.html'); });
    $router->get('/estruturas.html', function (): void { Response::redirect('/solucoes/estruturas.html'); });
    $router->get('/processo', function (): void { Response::redirect('/#processo'); });
    $router->get('/como-funciona', function (): void { Response::redirect('/#processo'); });

    // API pública de orçamentos -> alimenta diretamente o Funil de Vendas (Pipeline)
    $router->post('/api/orcamento', [OrcamentoController::class, 'store']);

    // Auth (Acesso exclusivo para administradores)
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
    
    // Cadastro externo desativado — redireciona para o login
    $router->get('/register', function (): void {
        Session::flash('error', 'O cadastro externo está desativado. Acesse com suas credenciais de administrador.');
        Response::redirect('/login');
    });
    $router->post('/register', function (): void {
        Session::flash('error', 'O cadastro externo está desativado.');
        Response::redirect('/login');
    });

    $router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class, AuthMiddleware::class]);

    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
    $router->post('/forgot-password', [AuthController::class, 'forgotPassword'], [CsrfMiddleware::class]);
    $router->get('/reset-password', [AuthController::class, 'showResetPassword']);
    $router->post('/reset-password', [AuthController::class, 'resetPassword'], [CsrfMiddleware::class]);

    // ----------------------------------------------------------------
    // Autenticado
    // ----------------------------------------------------------------
    $router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);

    // Perfil do usuário
    $router->get('/profile',          [ProfileController::class, 'show'],           [AuthMiddleware::class]);
    $router->put('/profile',          [ProfileController::class, 'update'],         [CsrfMiddleware::class, AuthMiddleware::class]);
    $router->put('/profile/password', [ProfileController::class, 'updatePassword'], [CsrfMiddleware::class, AuthMiddleware::class]);

    // Billing (assinatura via Stripe)
    $router->get('/plans', [SubscriptionController::class, 'plans']);
    $router->post('/billing/checkout/{id}', [SubscriptionController::class, 'checkout'], [CsrfMiddleware::class, AuthMiddleware::class]);
    $router->get('/billing/success', [SubscriptionController::class, 'success'], [AuthMiddleware::class]);
    $router->post('/billing/portal', [SubscriptionController::class, 'portal'], [CsrfMiddleware::class, AuthMiddleware::class]);

    // Webhook Stripe — sem CSRF, validado por assinatura Stripe
    $router->post('/billing/webhook', [WebhookController::class, 'handle']);

    // ----------------------------------------------------------------
    // Admin
    // ----------------------------------------------------------------
    $adminMiddlewares      = [CsrfMiddleware::class, AuthMiddleware::class, AdminMiddleware::class];
    $adminReadMiddlewares  = [AuthMiddleware::class, AdminMiddleware::class];

    $router->get('/admin', [DashboardController::class, 'index'], $adminReadMiddlewares);

    // Usuários
    $router->get('/admin/users',           [UserController::class, 'index'],   $adminReadMiddlewares);
    $router->get('/admin/users/{id}/edit', [UserController::class, 'edit'],    $adminReadMiddlewares);
    $router->put('/admin/users/{id}',      [UserController::class, 'update'],  $adminMiddlewares);
    $router->delete('/admin/users/{id}',   [UserController::class, 'destroy'], $adminMiddlewares);

    // Testemunhos
    $router->get('/admin/testimonials',              [TestimonialController::class, 'index'],   $adminReadMiddlewares);
    $router->get('/admin/testimonials/create',       [TestimonialController::class, 'create'],  $adminReadMiddlewares);
    $router->post('/admin/testimonials',             [TestimonialController::class, 'store'],   $adminMiddlewares);
    $router->get('/admin/testimonials/{id}/edit',    [TestimonialController::class, 'edit'],    $adminReadMiddlewares);
    $router->put('/admin/testimonials/{id}',         [TestimonialController::class, 'update'],  $adminMiddlewares);
    $router->delete('/admin/testimonials/{id}',      [TestimonialController::class, 'destroy'], $adminMiddlewares);

    // Logos de empresas
    $router->get('/admin/logos',              [CompanyLogoController::class, 'index'],   $adminReadMiddlewares);
    $router->get('/admin/logos/create',       [CompanyLogoController::class, 'create'],  $adminReadMiddlewares);
    $router->post('/admin/logos',             [CompanyLogoController::class, 'store'],   $adminMiddlewares);
    $router->get('/admin/logos/{id}/edit',    [CompanyLogoController::class, 'edit'],    $adminReadMiddlewares);
    $router->put('/admin/logos/{id}',         [CompanyLogoController::class, 'update'],  $adminMiddlewares);
    $router->delete('/admin/logos/{id}',      [CompanyLogoController::class, 'destroy'], $adminMiddlewares);

    // Webhooks
    $router->get('/admin/webhooks',               [WebhookAdminController::class, 'index'], $adminReadMiddlewares);
    $router->post('/admin/webhooks/{id}/retry',   [WebhookAdminController::class, 'retry'], $adminMiddlewares);

    // Funil de vendas (Kanban)
    $router->get('/admin/pipeline',                    [PipelineController::class, 'board'],   $adminReadMiddlewares);
    $router->get('/admin/pipeline/leads/create',        [PipelineController::class, 'create'],  $adminReadMiddlewares);
    $router->post('/admin/pipeline/leads',              [PipelineController::class, 'store'],   $adminMiddlewares);
    $router->get('/admin/pipeline/leads/{id}/edit',     [PipelineController::class, 'edit'],     $adminReadMiddlewares);
    $router->put('/admin/pipeline/leads/{id}',          [PipelineController::class, 'update'],   $adminMiddlewares);
    $router->delete('/admin/pipeline/leads/{id}',       [PipelineController::class, 'destroy'],  $adminMiddlewares);
    $router->post('/admin/pipeline/leads/{id}/move',    [PipelineController::class, 'move'],     $adminMiddlewares);

    // Etapas do funil
    $router->get('/admin/pipeline/stages',              [PipelineStageController::class, 'index'],   $adminReadMiddlewares);
    $router->get('/admin/pipeline/stages/create',        [PipelineStageController::class, 'create'],  $adminReadMiddlewares);
    $router->post('/admin/pipeline/stages',              [PipelineStageController::class, 'store'],   $adminMiddlewares);
    $router->get('/admin/pipeline/stages/{id}/edit',     [PipelineStageController::class, 'edit'],     $adminReadMiddlewares);
    $router->put('/admin/pipeline/stages/{id}',          [PipelineStageController::class, 'update'],   $adminMiddlewares);
    $router->delete('/admin/pipeline/stages/{id}',       [PipelineStageController::class, 'destroy'],  $adminMiddlewares);
};

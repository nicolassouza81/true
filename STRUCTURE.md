# STRUCTURE.md — Mapa do projeto (contexto para IA)

Cole este arquivo no início de qualquer conversa com IA para evitar reexplicar a arquitetura.

---

## Stack

PHP 8.1+ puro · MySQL/PDO · JS ES6 nativo · CSS custom properties · Stripe via cURL · sem Composer · compatível com Hostinger shared hosting.

---

## Convenções obrigatórias

| Tipo | Convenção | Exemplo |
|---|---|---|
| Controller | `XController` em `app/Controllers/` | `AuthController.php` |
| Controller admin | `app/Controllers/Admin/` | `UserController.php` |
| Controller billing | `app/Controllers/Billing/` | `SubscriptionController.php` |
| Model | `XModel extends QueryBuilder` | `UserModel.php` |
| View | `view_x.php` ou `index.php`/`form.php` dentro de pasta | `view_dashboard.php` |
| Service | `XService` em `app/Services/` | `StripeService.php` |
| Namespace core | `Core\X` → `core/X.php` | `Core\Auth` → `core/Auth.php` |
| Namespace app | `App\X\Y` → `app/X/Y.php` | `App\Models\UserModel` |

---

## Arquivos-chave

```
index.php              → Front Controller: bootstrap, sessão, headers, router
config/app.php         → Nome, branding, feature flags (editar ao clonar)
config/routes.php      → Todas as rotas (closures, middlewares)
core/Router.php        → dispatch(), rotas com {param}, middlewares
core/Request.php       → input(), param(), query(), ip(), isAjax()
core/Response.php      → view(), json(), redirect(), abort() (usa public/errors/*.php)
core/Session.php       → set/get/flash/csrfToken()
core/Auth.php          → attempt(), check(), user(), isAdmin() + session_version
core/Validator.php     → new Validator($data, $rules) → passes()/errors() + image_url, url
core/Cache.php         → Cache::remember($key, $ttl, fn) / Cache::forget($key)
core/RateLimiter.php   → tooManyAttempts(), hit(), clear()
core/Config.php        → Config::get('app.name'), Config::feature('billing')
core/Database.php      → Database::connection() → PDO singleton
core/Env.php           → Env::get(), Env::getBool(), Env::getInt()
core/Mailer.php        → fachada de e-mail — send(), sendPasswordReset(), sendWelcome()
core/Mail/MailDriverInterface.php → contrato de drivers de e-mail
core/Mail/MailgunDriver.php       → driver Mailgun via cURL (produção)
core/Mail/NativeMailDriver.php    → driver mail() nativo (dev/fallback)
core/Middleware/CsrfMiddleware.php   → valida _csrf em POST/PUT/DELETE
core/Middleware/AuthMiddleware.php   → redireciona /login se não autenticado
core/Middleware/AdminMiddleware.php  → aborta 403 se não for admin
app/Models/QueryBuilder.php          → find/findBy/all/count/insert/update/delete
app/Services/StripeService.php       → createCheckoutSession, verifyWebhookSignature
database/migrate.php                 → runner de migrations (php database/migrate.php)
database/seeds/seed.php              → dados de exemplo (php database/seeds/seed.php)
check.php                            → verificação de ambiente pré-deploy
setup.sh                             → setup completo (bash setup.sh [--seed])
Makefile                             → atalhos: make setup / seed / migrate / serve / cache-clear
public/errors/404.php                → página de erro 404 com layout
public/errors/500.php                → página de erro 500 com layout
```

---

## Fluxo de uma requisição

```
index.php
  → Env::load() → Session::start() → headers de segurança
  → Router::dispatch(Request)
    → match rota → run middlewares → call [Controller, 'method']
      → Controller usa Models/Services
        → Response::view('path/view', $data, 'layout') | json() | redirect()
```

---

## Criar nova entidade (ex: "Product")

1. Migration: `database/migrations/009_create_products.sql`
2. Model: `app/Models/ProductModel.php extends QueryBuilder` (tabela: `products`)
3. Controller admin: `app/Controllers/Admin/ProductController.php extends CrudController`
   - Defina `$table`, `$viewPrefix`, `$routePrefix` e `fields()`
4. Views: `views/admin/products/index.php` e `form.php`
5. Rotas: adicionar em `config/routes.php` com middleware `AdminMiddleware`

---

## Entidades de conteúdo da home

| Entidade | Model | Controller | Rotas |
|---|---|---|---|
| Testemunhos | `TestimonialModel` | `Admin/TestimonialController` | `/admin/testimonials` |
| Logos | `CompanyLogoModel` | `Admin/CompanyLogoController` | `/admin/logos` |

Ambos têm cache de 10 min via `Cache::remember()` no `HomeController`.
Para invalidar o cache após salvar, chame `Cache::forget('home.testimonials')` / `Cache::forget('home.logos')` nos controllers admin (melhoria futura).

---

## Funil de vendas (Kanban)

Módulo de CRM simples: leads avançam por etapas configuráveis em um quadro
Kanban com drag-and-drop (`/admin/pipeline`). Atrás da flag `features.pipeline`
em `config/app.php`.

| Entidade | Model | Tabela | Controller |
|---|---|---|---|
| Etapas do funil | `PipelineStageModel` | `pipeline_stages` | `Admin/PipelineStageController` (`/admin/pipeline/stages`) |
| Leads (cards) | `LeadModel` | `leads` | `Admin/PipelineController` (`/admin/pipeline`) |
| Histórico de movimentação | `LeadStageHistoryModel` | `lead_stage_history` | registrado automaticamente a cada mudança de etapa |

- Drag-and-drop: `public/js/modules/pipeline.js` (HTML5 DnD nativo, sem lib).
  Move o card para o **fim** da coluna de destino — não há reordenação fina
  dentro da mesma coluna ainda (extensão futura: usar `sort_order` fracionário
  ou recalcular ao soltar entre dois cards específicos).
- `leads.stage_id` tem `ON DELETE RESTRICT` — uma etapa com leads não pode ser
  excluída; `PipelineStageController::destroy` já trata isso com mensagem
  amigável antes de deixar a FK estourar.
- Toda movimentação (drag-and-drop, ou troca da etapa no formulário de
  edição) grava uma linha em `lead_stage_history` via `LeadStageHistoryModel::record()`.
- O endpoint `POST /admin/pipeline/leads/{id}/move` é AJAX e usa o header
  `X-CSRF-Token` (não campo de formulário). Como o `CsrfMiddleware` rotaciona
  o token a cada escrita, a resposta devolve o token novo (`csrf` no JSON) e
  o JS atualiza a variável local — sem isso, o segundo drag da mesma página
  cairia em 419.

---

## Erros comuns e soluções

| Erro | Causa | Solução |
|---|---|---|
| `PDOException: could not find driver` | Extensão `pdo_mysql` não instalada | `php check.php` para diagnóstico; instalar ext |
| `PDOException: Access denied` | Credenciais erradas no `.env` | Verificar `DB_USERNAME` / `DB_PASSWORD` |
| `PDOException: Unknown database` | Banco não criado | Criar o banco no MySQL antes de rodar migrations |
| Página em branco (500 silencioso) | `APP_DEBUG=false` ocultando erro | Temporariamente `APP_DEBUG=true`; checar `storage/logs/php_errors.log` |
| Rota retorna 404 | `mod_rewrite` desativado ou `.htaccess` ignorado | Ativar `mod_rewrite`; conferir `AllowOverride All` no vhost |
| CSRF inválido após submit | Token rotacionado ou sessão expirada | Normal — usuário deve recarregar o formulário |
| Cache desatualizado na home | Admin editou conteúdo mas cache não invalidou | `make cache-clear` ou checar se `Cache::forget()` foi chamado |
| `Auth::check()` derruba sessão | `session_version` no banco diverge da sessão | Esperado após troca de senha — usuário precisa logar novamente |
| Webhook Stripe retorna 400 | `STRIPE_WEBHOOK_SECRET` errado ou ausente | Verificar secret no Stripe Dashboard e no `.env` |
| E-mail de reset não chega | `MAIL_DRIVER=native` em produção cai em spam | Configurar `MAIL_DRIVER=mailgun` + `MAILGUN_API_KEY` + `MAILGUN_DOMAIN` |
| Mailgun retorna 401 | API key inválida ou domínio errado | Verificar no Mailgun Dashboard → Sending → Domains |
| Mailgun retorna 403 | Domínio não verificado no Mailgun | Adicionar registros DNS (SPF, DKIM) no painel do domínio |

---

```
/plans (GET) → view_plans.php
  → POST /billing/checkout/{plan_id}
    → StripeService::createCheckoutSession()
    → redirect para URL do Stripe
      → Stripe redireciona para /billing/success?session_id=...
      → Stripe envia webhook POST /billing/webhook
        → WebhookController::handle()
          → verifyWebhookSignature()
          → WebhookEventModel::record() (idempotência)
          → SubscriptionModel::upsertFromStripe()
```

---

## Branding / clone para novo projeto

Editar apenas `config/app.php`:
- `name`, `branding.logo`, `branding.favicon`, `branding.primary_color`, `branding.accent_color`
- Feature flags em `features`

Editar `public/css/tokens.css`:
- `--color-primary`, `--color-primary-hover`, `--font-sans`

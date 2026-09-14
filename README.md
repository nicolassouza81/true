# Projeto Base — PHP MVC + MySQL + Stripe

Boilerplate para criação rápida de sites/SaaS com autenticação, painel admin e assinatura via Stripe. Compatível com **Hostinger Shared Hosting** (PHP puro, zero Composer).

---

## Stack

- **PHP 8.1+** puro (sem frameworks, sem Composer)
- **MySQL** com PDO (prepared statements)
- **CSS** com design tokens + arquitetura modular
- **JS** ES6 modules nativos (sem bundler)
- **Stripe** via cURL puro (Checkout + Webhooks)

---

## Setup local

```bash
# 1. Clone o projeto
git clone <repo> meu-projeto && cd meu-projeto

# 2. Setup completo (copia .env, verifica ambiente, roda migrations)
bash setup.sh

# ou com dados de exemplo (admin + plano + testemunhos + logos):
bash setup.sh --seed

# 3. Suba o servidor local
make serve   # ou: php -S localhost:8000
```

### Atalhos com Make

```bash
make setup        # setup inicial (sem seed)
make seed         # setup + dados de exemplo
make migrate      # roda migrations pendentes
make check        # verifica ambiente
make serve        # servidor php -S localhost:8000
make cache-clear  # limpa storage/cache
```

---

## Deploy na Hostinger

1. Faça upload de todos os arquivos na raiz `public_html/` (ou subpasta do domínio).
2. Certifique-se de que o `.htaccess` está presente e que `mod_rewrite` está ativo (padrão na Hostinger).
3. Crie o banco MySQL pelo hPanel e preencha as credenciais no `.env`.
4. Execute as migrations via SSH ou pelo terminal da Hostinger:
   ```bash
   php database/migrate.php
   ```
5. Defina `APP_DEBUG=false` e `APP_ENV=production` no `.env` em produção.

### Importante: diretórios protegidos

O `.htaccess` bloqueia acesso direto a `storage/`, `database/`, `config/` e `core/`.
Confirme que nenhum desses diretórios é acessível via browser antes de ir ao ar.

---

## Variáveis de ambiente (`.env`)

| Variável | Descrição |
|---|---|
| `APP_NAME` | Nome da aplicação |
| `APP_ENV` | `local` ou `production` |
| `APP_DEBUG` | `true` / `false` |
| `APP_URL` | URL base (sem barra final) |
| `TRUSTED_PROXIES` | IPs de proxy/CDN confiáveis (separados por vírgula). Só requisições vindas desses IPs têm o header `X-Forwarded-For` respeitado em `Request::ip()` — deixe vazio se a app recebe tráfego direto. Sem isso configurado corretamente atrás de um proxy real, ou preenchido sem um proxy na frente, o rate limit de login/reset e o IP do audit log podem ficar incorretos |
| `DB_HOST` | Host do MySQL |
| `DB_PORT` | Porta (padrão `3306`) |
| `DB_DATABASE` | Nome do banco |
| `DB_USERNAME` | Usuário do banco |
| `DB_PASSWORD` | Senha do banco |
| `SESSION_NAME` | Nome do cookie de sessão |
| `SESSION_LIFETIME` | Duração em minutos |
| `STRIPE_PUBLIC_KEY` | Chave pública Stripe |
| `STRIPE_SECRET_KEY` | Chave secreta Stripe |
| `STRIPE_WEBHOOK_SECRET` | Secret do webhook Stripe |
| `MAIL_FROM` | E-mail remetente |
| `MAIL_FROM_NAME` | Nome do remetente |
| `RATE_LIMIT_MAX_ATTEMPTS` | Tentativas antes de bloquear (padrão `5`) |
| `RATE_LIMIT_DECAY_MINUTES` | Minutos de bloqueio (padrão `15`) |

---

## Feature flags (`config/app.php`)

Desative módulos sem remover código:

```php
'features' => [
    'billing'     => true,  // false = remove módulo Stripe
    'admin_panel' => true,
    'audit_log'   => true,
    'rate_limit'  => true,
    'pipeline'    => true,  // false = remove módulo de funil (Kanban)
],
```

---

## Funil de vendas (Kanban)

CRM simples em `/admin/pipeline`: leads avançam por etapas configuráveis
(`/admin/pipeline/stages`) num quadro Kanban com drag-and-drop. Cada
movimentação de etapa fica registrada em histórico. Detalhes de
implementação (schema, endpoints, limitações conhecidas) em `STRUCTURE.md`.

Para começar a usar: rode as migrations (`php database/migrate.php`) e,
opcionalmente, o seed (`bash setup.sh --seed`) para já ter etapas padrão
(Novo lead → Contato → Proposta → Ganho/Perdido) e 3 leads de exemplo.

---

## CSRF: comportamento com múltiplas abas

O token CSRF roda a cada POST/PUT/DELETE bem-sucedido (`CsrfMiddleware`), como proteção contra replay. Efeito colateral esperado: se o usuário tem dois formulários abertos em abas diferentes e submete o da aba A, o token da aba B fica obsoleto — o próximo submit nela retorna 419 ("Token CSRF inválido ou expirado"). Não é um bug; é a troca consciente entre proteção contra replay e conforto de uso com múltiplas abas. Se isso gerar muito suporte para um cliente específico, a alternativa é parar de rotacionar em toda escrita e rotacionar só em ações sensíveis (login, troca de senha).

---

## Configurar e-mail com Mailgun

O sistema suporta dois drivers de e-mail configurados via `MAIL_DRIVER` no `.env`:

| Driver | Quando usar |
|---|---|
| `native` | Desenvolvimento local ou testes |
| `mailgun` | Produção — entregabilidade garantida |

### Configurar Mailgun

1. Crie uma conta em [mailgun.com](https://mailgun.com) (plano gratuito: 1000 e-mails/mês)
2. Adicione e verifique seu domínio (DNS: SPF + DKIM)
3. Copie a **API Key** em Account → API Keys
4. Preencha o `.env`:

```env
MAIL_DRIVER=mailgun
MAILGUN_API_KEY=key-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MAILGUN_DOMAIN=mg.seudominio.com.br
MAILGUN_REGION=us   # ou 'eu' se seu domínio está na região europeia
```

5. Verifique com `php check.php` — Mailgun deve aparecer como OK.

---

## Configurar Stripe Webhook

No [Stripe Dashboard](https://dashboard.stripe.com/webhooks), adicione o endpoint:
```
https://seudominio.com/billing/webhook
```

Eventos necessários:
- `checkout.session.completed`
- `customer.subscription.updated`
- `customer.subscription.deleted`

Copie o **Signing Secret** gerado para `STRIPE_WEBHOOK_SECRET` no `.env`.

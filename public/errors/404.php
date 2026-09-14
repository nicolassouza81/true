<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página não encontrada</title>
    <link rel="stylesheet" href="/public/css/tokens.css">
    <link rel="stylesheet" href="/public/css/reset.css">
    <link rel="stylesheet" href="/public/css/base.css">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: var(--space-8);
        }
        .error-page__code {
            font-size: 7rem;
            font-weight: var(--font-bold);
            color: var(--color-border);
            line-height: 1;
            margin-bottom: var(--space-4);
        }
        .error-page__title {
            font-size: var(--text-2xl);
            font-weight: var(--font-semibold);
            margin-bottom: var(--space-3);
        }
        .error-page__message {
            color: var(--color-text-muted);
            margin-bottom: var(--space-8);
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div>
            <p class="error-page__code">404</p>
            <h1 class="error-page__title">Página não encontrada</h1>
            <p class="error-page__message">
                A página que você procura não existe ou foi movida.
            </p>
            <a href="/" class="btn btn--primary">Voltar ao início</a>
        </div>
    </div>
</body>
</html>

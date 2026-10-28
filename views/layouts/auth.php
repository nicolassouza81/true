<!DOCTYPE html>
<html lang="<?= \Core\Config::get('app.locale.language', 'pt-BR') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Acesso') ?> — <?= htmlspecialchars(\Core\Config::get('app.name', 'TRUE Eventos')) ?></title>
    <link rel="icon" href="<?= \Core\Config::get('app.branding.favicon') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=DM+Sans:ital,opsz,wght@0,9..40,300..800;1,9..40,300..800&display=swap" rel="stylesheet">
    <style>
    /* =============================================================
       TRUE EVENTOS — Layout de Autenticação (Standalone)
       ============================================================= */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --ink:            #080808;
        --surface-card:   #111111;
        --surface-input:  #0d0d0d;
        --line:           rgba(255,255,255,0.10);
        --line-strong:    rgba(255,255,255,0.18);
        --white:          #ffffff;
        --muted:          #888;
        --orange:         #ff4b19;
        --orange-glow:    rgba(255,75,25,0.28);
        --acid:           #dcff36;
        --display:        "Archivo Black", Impact, sans-serif;
        --body:           "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    html, body { height: 100%; }

    body {
        background-color: var(--ink);
        color: #d4d4d4;
        font-family: var(--body);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }

    h1, h2, h3, strong, b { font-synthesis: none; }

    /* Orange glow — top-left */
    body::after {
        content: "";
        position: fixed;
        top: -30vh;
        left: -20vw;
        width: 80vw;
        height: 80vw;
        background: radial-gradient(ellipse, rgba(255,75,25,0.09) 0%, transparent 65%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }

    /* Acid glow — bottom-right */
    .auth-glow-br {
        position: fixed;
        bottom: -25vh;
        right: -15vw;
        width: 60vw;
        height: 60vw;
        background: radial-gradient(ellipse, rgba(220,255,54,0.04) 0%, transparent 65%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }

    /* ---- Wrapper ---- */
    .auth-wrapper {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 440px;
        padding: 1.5rem;
        animation: auth-rise 0.5s cubic-bezier(0.22,1,0.36,1) both;
    }

    @keyframes auth-rise {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ---- Logo ---- */
    .auth-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2.5rem;
    }

    .auth-brand img {
        height: 32px;
        width: auto;
    }

    /* ---- Card ---- */
    .auth-card {
        background: var(--surface-card);
        border: 1px solid var(--line-strong);
        border-radius: 12px;
        padding: 2.5rem 2rem;
        box-shadow:
            0 0 0 1px rgba(255,75,25,0.06),
            0 32px 64px rgba(0,0,0,0.7),
            0 4px 16px rgba(0,0,0,0.5);
        position: relative;
    }

    .auth-page.container .auth-card {
        padding: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
    }

    .auth-card__title {
        font-family: var(--display);
        font-weight: 400;
        font-synthesis: none;
        font-size: 1.75rem;
        line-height: 1.1;
        letter-spacing: -0.02em;
        text-transform: uppercase;
        color: var(--white);
        margin-bottom: 1.25rem;
    }

    .form {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .form__group {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .form__label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.10em;
        color: var(--muted);
        font-family: var(--body);
    }

    .form__input {
        width: 100%;
        padding: 0.8rem 1rem;
        background: var(--surface-input);
        border: 1px solid var(--line-strong);
        border-radius: 6px;
        color: var(--white);
        font-family: var(--body);
        font-size: 0.9375rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form__input:focus {
        outline: none;
        border-color: var(--orange);
        background: #111;
        box-shadow: 0 0 0 3px var(--orange-glow);
    }

    .btn--primary {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 0.9rem 1.5rem;
        background: var(--orange);
        color: var(--white);
        border: none;
        border-radius: 6px;
        font-family: var(--body);
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        cursor: pointer;
        transition: all 0.22s ease;
    }

    .btn--primary:hover {
        background: var(--acid);
        color: var(--ink);
    }

    .auth-card__switch {
        margin-top: 1.25rem;
        text-align: center;
        font-size: 0.825rem;
    }

    .auth-card__switch a {
        color: var(--muted);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .auth-card__switch a:hover {
        color: var(--orange);
    }

    .alert {
        padding: 0.85rem 1.1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
    }

    .alert--error {
        background: rgba(255,75,25,0.10);
        border: 1px solid rgba(255,75,25,0.35);
        color: #ff8a6a;
    }

    .alert--success {
        background: rgba(220,255,54,0.08);
        border: 1px solid rgba(220,255,54,0.35);
        color: var(--acid);
    }

    /* Bottom orange accent line */
    .auth-card::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 10%;
        right: 10%;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--orange), transparent);
        pointer-events: none;
    }

    /* ---- Back to site ---- */
    .auth-back {
        display: flex;
        justify-content: center;
        margin-top: 1.75rem;
    }

    .auth-back a {
        font-size: 0.8rem;
        color: var(--muted);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: color 0.2s ease;
    }

    .auth-back a:hover { color: var(--white); }
    </style>
</head>
<body>
    <div class="auth-glow-br"></div>

    <div class="auth-wrapper">
        <!-- Logo -->
        <div class="auth-brand">
            <a href="/">
                <img src="/public/assets/brand/true-horizontal-white.svg"
                     alt="<?= htmlspecialchars(\Core\Config::get('app.name', 'TRUE Eventos')) ?>">
            </a>
        </div>

        <!-- Content card -->
        <div class="auth-card">
            <?php if ($flash = \Core\Session::getFlash('success')): ?>
                <div style="
                    padding:.85rem 1.1rem;border-radius:6px;font-size:.875rem;
                    font-weight:600;margin-bottom:1.5rem;display:flex;align-items:center;gap:.6rem;
                    background:rgba(220,255,54,.08);border:1px solid rgba(220,255,54,.35);
                    color:#dcff36;font-family:var(--body);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= htmlspecialchars((string) $flash) ?>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>

        <!-- Back link -->
        <div class="auth-back">
            <a href="/">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Voltar ao site
            </a>
        </div>
    </div>
</body>
</html>

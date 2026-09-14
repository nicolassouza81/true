<!DOCTYPE html>
<html lang="<?= \Core\Config::get('app.locale.language', 'pt-BR') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Acesso') ?> — <?= htmlspecialchars(\Core\Config::get('app.name', 'TRUE Eventos')) ?></title>
    <link rel="icon" href="<?= \Core\Config::get('app.branding.favicon') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
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
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }

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

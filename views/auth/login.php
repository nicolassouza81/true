<!-- ======================================================
     TRUE EVENTOS — Tela de Login
     Renderizado dentro de views/layouts/auth.php
     ====================================================== -->
<style>
/* ---- Auth card inner styles ---- */
.auth-eyebrow {
    font-family: "DM Sans", sans-serif;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: #ff4b19;
    margin-bottom: 0.45rem;
}

.auth-title {
    font-family: "Archivo Black", Impact, sans-serif;
    font-size: 1.85rem;
    line-height: 1.1;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    color: #ffffff;
    margin-bottom: 0.4rem;
}

.auth-subtitle {
    font-size: 0.875rem;
    color: #888;
    margin-bottom: 1.75rem;
    line-height: 1.5;
}

.auth-divider {
    height: 1px;
    background: rgba(255,255,255,0.10);
    margin-bottom: 1.75rem;
}

/* Error alert */
.auth-alert {
    padding: 0.85rem 1.1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: rgba(255,75,25,0.10);
    border: 1px solid rgba(255,75,25,0.35);
    color: #ff8a6a;
    font-family: "DM Sans", sans-serif;
}

/* Form */
.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.35rem;
}

.auth-form .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.auth-form label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.10em;
    color: #888;
    font-family: "DM Sans", sans-serif;
}

.auth-form input[type="email"],
.auth-form input[type="password"] {
    width: 100%;
    padding: 0.8rem 1rem;
    background: #0d0d0d;
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 6px;
    color: #ffffff;
    font-family: "DM Sans", sans-serif;
    font-size: 0.9375rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    box-sizing: border-box;
}

.auth-form input[type="email"]::placeholder,
.auth-form input[type="password"]::placeholder {
    color: rgba(255,255,255,0.18);
}

.auth-form input[type="email"]:focus,
.auth-form input[type="password"]:focus {
    outline: none;
    border-color: #ff4b19;
    background: #111;
    box-shadow: 0 0 0 3px rgba(255,75,25,0.28);
}

/* Footer row */
.auth-form__footer {
    display: flex;
    justify-content: flex-end;
    margin-top: -0.5rem;
}

.auth-form__footer a {
    font-size: 0.8rem;
    font-weight: 500;
    color: #888;
    text-decoration: none;
    transition: color 0.2s ease;
    font-family: "DM Sans", sans-serif;
}

.auth-form__footer a:hover {
    color: #ff4b19;
}

/* Submit button */
.auth-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    width: 100%;
    padding: 0.9rem 1.5rem;
    background: #ff4b19;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-family: "DM Sans", sans-serif;
    font-size: 0.875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: all 0.22s ease;
    margin-top: 0.15rem;
}

.auth-submit:hover {
    background: #dcff36;
    color: #080808;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(220,255,54,0.22);
}

.auth-submit:active {
    transform: translateY(0);
}

.auth-submit svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    transition: transform 0.22s ease;
}

.auth-submit:hover svg {
    transform: translateX(3px);
}
</style>

<!-- Eyebrow + Title -->
<p class="auth-eyebrow">Painel Administrativo</p>
<h1 class="auth-title">Bem-vindo<br>de volta</h1>
<p class="auth-subtitle">Entre com suas credenciais para acessar o painel.</p>

<div class="auth-divider"></div>

<?php if (!empty($error)): ?>
    <div class="auth-alert" role="alert">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= htmlspecialchars((string) $error) ?>
    </div>
<?php endif; ?>

<form action="/login" method="POST" class="auth-form" novalidate>
    <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">

    <div class="form-group">
        <label for="email">E-mail</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="seu@email.com"
            value="<?= htmlspecialchars((string) ($old['email'] ?? '')) ?>"
            autocomplete="email"
            required
        >
    </div>

    <div class="form-group">
        <label for="password">Senha</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="••••••••"
            autocomplete="current-password"
            required
        >
    </div>

    <div class="auth-form__footer">
        <a href="/forgot-password">Esqueci minha senha</a>
    </div>

    <button type="submit" class="auth-submit">
        Entrar no Painel
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
    </button>
</form>

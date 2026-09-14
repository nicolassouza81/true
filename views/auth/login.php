<section class="auth-page container">
    <div class="auth-card">
        <h1 class="auth-card__title">Acesso Administrativo</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error"><?= htmlspecialchars((string) $error) ?></div>
        <?php endif; ?>

        <form action="/login" method="POST" class="form" novalidate>
            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">

            <div class="form__group">
                <label for="email" class="form__label">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form__input"
                    value="<?= htmlspecialchars((string) ($old['email'] ?? '')) ?>"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form__group">
                <label for="password" class="form__label">Senha</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form__input"
                    autocomplete="current-password"
                    required
                >
            </div>

            <div class="form__footer">
                <a href="/forgot-password" class="form__link">Esqueci minha senha</a>
                <a href="/" class="form__link">← Voltar ao site</a>
            </div>

            <button type="submit" class="btn btn--primary btn--full">Entrar no Painel</button>
        </form>
    </div>
</section>

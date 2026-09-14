<section class="auth-page container">
    <div class="auth-card">
        <h1 class="auth-card__title">Criar conta</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error"><?= htmlspecialchars((string) $error) ?></div>
        <?php endif; ?>

        <form action="/register" method="POST" class="form" novalidate>
            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">

            <div class="form__group">
                <label for="name" class="form__label">Nome</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form__input"
                    value="<?= htmlspecialchars((string) ($old['name'] ?? '')) ?>"
                    autocomplete="name"
                    required
                >
            </div>

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
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
            </div>

            <div class="form__group">
                <label for="password_confirmation" class="form__label">Confirmar senha</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form__input"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit" class="btn btn--primary btn--full">Criar conta</button>
        </form>

        <p class="auth-card__switch">Já tem conta? <a href="/login">Entrar</a></p>
    </div>
</section>

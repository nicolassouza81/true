<section class="auth-page container">
    <div class="auth-card">
        <h1 class="auth-card__title">Redefinir senha</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error"><?= htmlspecialchars((string) $error) ?></div>
        <?php endif; ?>

        <form action="/reset-password" method="POST" class="form" novalidate>
            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars((string) ($token ?? '')) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars((string) ($email ?? '')) ?>">

            <div class="form__group">
                <label for="password" class="form__label">Nova senha</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form__input"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="form__group">
                <label for="password_confirmation" class="form__label">Confirmar nova senha</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form__input"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit" class="btn btn--primary btn--full">Redefinir senha</button>
        </form>
    </div>
</section>

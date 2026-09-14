<section class="auth-page container">
    <div class="auth-card">
        <h1 class="auth-card__title">Recuperar senha</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error"><?= htmlspecialchars((string) $error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert--success"><?= htmlspecialchars((string) $success) ?></div>
        <?php endif; ?>

        <form action="/forgot-password" method="POST" class="form" novalidate>
            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">

            <div class="form__group">
                <label for="email" class="form__label">E-mail cadastrado</label>
                <input type="email" id="email" name="email" class="form__input" autocomplete="email" required>
            </div>

            <button type="submit" class="btn btn--primary btn--full">Enviar instruções</button>
        </form>

        <p class="auth-card__switch"><a href="/login">Voltar para o login</a></p>
    </div>
</section>

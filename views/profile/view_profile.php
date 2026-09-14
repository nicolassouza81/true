<div class="profile-page container">

    <?php if (!empty($success)): ?>
        <div class="alert alert--success"><?= htmlspecialchars((string) $success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert--error"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>

    <div class="profile-grid">

        <!-- Dados pessoais -->
        <section class="card">
            <h2 class="card__title">Dados pessoais</h2>

            <form action="/profile" method="POST" class="form">
                <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
                <input type="hidden" name="_method" value="PUT">

                <div class="form__group">
                    <label class="form__label">Nome</label>
                    <input type="text" name="name" class="form__input"
                           value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                           required maxlength="120">
                </div>

                <div class="form__group">
                    <label class="form__label">E-mail</label>
                    <input type="email" class="form__input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                    <span class="form__hint">O e-mail não pode ser alterado. Entre em contato com o suporte se necessário.</span>
                </div>

                <div class="form__group">
                    <label class="form__label">Membro desde</label>
                    <input type="text" class="form__input"
                           value="<?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '—' ?>"
                           disabled>
                </div>

                <button type="submit" class="btn btn--primary">Salvar alterações</button>
            </form>
        </section>

        <!-- Alterar senha -->
        <section class="card">
            <h2 class="card__title">Alterar senha</h2>

            <form action="/profile/password" method="POST" class="form">
                <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
                <input type="hidden" name="_method" value="PUT">

                <div class="form__group">
                    <label class="form__label">Senha atual</label>
                    <input type="password" name="current_password" class="form__input"
                           autocomplete="current-password" required>
                </div>

                <div class="form__group">
                    <label class="form__label">Nova senha</label>
                    <input type="password" name="password" class="form__input"
                           autocomplete="new-password" minlength="8" required>
                    <span class="form__hint">Mínimo de 8 caracteres.</span>
                </div>

                <div class="form__group">
                    <label class="form__label">Confirmar nova senha</label>
                    <input type="password" name="password_confirmation" class="form__input"
                           autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn btn--primary">Alterar senha</button>
            </form>
        </section>

    </div>
</div>

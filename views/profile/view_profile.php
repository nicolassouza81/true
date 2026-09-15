<style>
.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
    gap: 1.8rem;
    align-items: start;
}
.form__hint {
    display: block;
    font-size: 0.78rem;
    color: var(--muted-dim);
    margin-top: 0.4rem;
}
</style>

<div class="profile-grid">
    <!-- Dados pessoais -->
    <section class="card">
        <h2 class="card__title" style="border-bottom: 1px solid var(--line); padding-bottom: 0.75rem; margin-bottom: 1.5rem;">Dados Pessoais</h2>

        <form action="/profile" method="POST" class="form">
            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
            <input type="hidden" name="_method" value="PUT">

            <div class="form__group">
                <label class="form__label">Nome Completo</label>
                <input type="text" name="name" class="form__input"
                       value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                       required maxlength="120">
            </div>

            <div class="form__group">
                <label class="form__label">E-mail Corporativo</label>
                <input type="email" class="form__input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
                <span class="form__hint">O e-mail não pode ser alterado. Entre em contato com o suporte se necessário.</span>
            </div>

            <div class="form__group">
                <label class="form__label">Membro Desde</label>
                <input type="text" class="form__input"
                       value="<?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '—' ?>"
                       disabled style="opacity: 0.6; cursor: not-allowed;">
            </div>

            <div style="margin-top: 1.8rem;">
                <button type="submit" class="btn btn--primary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Salvar Alterações</span>
                </button>
            </div>
        </form>
    </section>

    <!-- Alterar senha -->
    <section class="card">
        <h2 class="card__title" style="border-bottom: 1px solid var(--line); padding-bottom: 0.75rem; margin-bottom: 1.5rem;">Alterar Senha</h2>

        <form action="/profile/password" method="POST" class="form">
            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
            <input type="hidden" name="_method" value="PUT">

            <div class="form__group">
                <label class="form__label">Senha Atual</label>
                <input type="password" name="current_password" class="form__input"
                       autocomplete="current-password" required placeholder="••••••••">
            </div>

            <div class="form__group">
                <label class="form__label">Nova Senha</label>
                <input type="password" name="password" class="form__input"
                       autocomplete="new-password" minlength="8" required placeholder="Mínimo de 8 caracteres">
                <span class="form__hint">Mínimo de 8 caracteres.</span>
            </div>

            <div class="form__group">
                <label class="form__label">Confirmar Nova Senha</label>
                <input type="password" name="password_confirmation" class="form__input"
                       autocomplete="new-password" required placeholder="Confirme a nova senha">
            </div>

            <div style="margin-top: 1.8rem;">
                <button type="submit" class="btn btn--primary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Alterar Senha</span>
                </button>
            </div>
        </form>
    </section>
</div>

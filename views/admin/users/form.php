<div class="admin-section">
    <div class="admin-section__header">
        <h2>Editar usuário</h2>
        <a href="/admin/users" class="btn btn--secondary btn--sm">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Voltar
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert--error"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>

    <form action="/admin/users/<?= (int) $user['id'] ?>" method="POST" class="form form--admin">
        <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
        <input type="hidden" name="_method" value="PUT">

        <div class="form-grid">
            <div class="form__group">
                <label class="form__label">Nome <span class="required">*</span></label>
                <input type="text" name="name" class="form__input"
                       value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="form__group">
                <label class="form__label">E-mail <span class="required">*</span></label>
                <input type="email" name="email" class="form__input"
                       value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form__group">
                <label class="form__label">Role <span class="required">*</span></label>
                <select name="role_id" class="form__input form__select" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int) $role['id'] ?>"
                            <?= (int) $user['role_id'] === (int) $role['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form__group">
                <label class="form__label">Status <span class="required">*</span></label>
                <select name="status" class="form__input form__select" required>
                    <?php foreach (['active' => 'Ativo', 'inactive' => 'Inativo', 'banned' => 'Banido'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $user['status'] === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form__group form-grid__full">
                <label class="form__label">Nova senha</label>
                <input type="password" name="password" class="form__input"
                       autocomplete="new-password" minlength="8"
                       placeholder="Deixe em branco para manter a senha atual">
                <span class="form__hint">
                    Preencha apenas para alterar. Ao salvar, todas as sessões ativas deste usuário serão invalidadas.
                </span>
            </div>
        </div>

        <div class="form__actions">
            <button type="submit" class="btn btn--primary">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Salvar alterações
            </button>
            <a href="/admin/users" class="btn btn--secondary">Cancelar</a>
        </div>
    </form>
</div>

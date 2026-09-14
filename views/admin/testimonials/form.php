<?php
$isEdit  = $item !== null;
$action  = $isEdit ? "/admin/testimonials/{$item['id']}" : '/admin/testimonials';
$old     = $old ?? [];
$val     = fn (string $k) => htmlspecialchars((string) ($old[$k] ?? $item[$k] ?? ''));
?>

<div class="admin-section">
    <div class="admin-section__header">
        <h2><?= htmlspecialchars($title) ?></h2>
        <a href="/admin/testimonials" class="btn btn--secondary btn--sm">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Voltar
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert--error"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>

    <form action="<?= $action ?>" method="POST" class="form form--admin">
        <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form__group">
                <label class="form__label">Nome do autor <span class="required">*</span></label>
                <input type="text" name="author_name" class="form__input" value="<?= $val('author_name') ?>" required>
            </div>

            <div class="form__group">
                <label class="form__label">Cargo / Empresa</label>
                <input type="text" name="author_title" class="form__input" value="<?= $val('author_title') ?>" placeholder="CEO, Empresa XYZ">
            </div>

            <div class="form__group form-grid__full">
                <label class="form__label">URL do avatar (imagem)</label>
                <input type="url" name="author_avatar" class="form__input" value="<?= $val('author_avatar') ?>" placeholder="https://...">
                <span class="form__hint">URL de imagem pública. Deixe em branco para usar iniciais.</span>
            </div>

            <div class="form__group form-grid__full">
                <label class="form__label">Depoimento <span class="required">*</span></label>
                <textarea name="content" class="form__input form__textarea" rows="4" required><?= $val('content') ?></textarea>
            </div>

            <div class="form__group">
                <label class="form__label">Avaliação (1–5 estrelas)</label>
                <select name="rating" class="form__input form__select">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= (int) ($old['rating'] ?? $item['rating'] ?? 5) === $i ? 'selected' : '' ?>>
                            <?= str_repeat('★', $i) ?> (<?= $i ?>)
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form__group">
                <label class="form__label">Ordem de exibição</label>
                <input type="number" name="sort_order" class="form__input" min="0"
                       value="<?= (int) ($old['sort_order'] ?? $item['sort_order'] ?? 0) ?>">
            </div>

            <div class="form__group">
                <label class="form__label checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= !empty($old) ? (isset($old['is_active']) ? 'checked' : '') : ($item['is_active'] ?? 1 ? 'checked' : '') ?>>
                    Ativo (exibir na home)
                </label>
            </div>
        </div>

        <div class="form__actions">
            <button type="submit" class="btn btn--primary">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <?= $isEdit ? 'Salvar alterações' : 'Criar testemunho' ?>
            </button>
            <a href="/admin/testimonials" class="btn btn--secondary">Cancelar</a>
        </div>
    </form>
</div>

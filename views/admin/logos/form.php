<?php
$isEdit = $item !== null;
$action = $isEdit ? "/admin/logos/{$item['id']}" : '/admin/logos';
$old    = $old ?? [];
$val    = fn (string $k) => htmlspecialchars((string) ($old[$k] ?? $item[$k] ?? ''));
?>

<div class="admin-section">
    <div class="admin-section__header">
        <h2><?= htmlspecialchars($title) ?></h2>
        <a href="/admin/logos" class="btn btn--secondary btn--sm">
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
                <label class="form__label">Nome da empresa <span class="required">*</span></label>
                <input type="text" name="name" class="form__input" value="<?= $val('name') ?>" required>
            </div>

            <div class="form__group">
                <label class="form__label">URL do site</label>
                <input type="url" name="website_url" class="form__input" value="<?= $val('website_url') ?>" placeholder="https://empresa.com.br">
            </div>

            <div class="form__group form-grid__full">
                <label class="form__label">URL do logo <span class="required">*</span></label>
                <input type="url" name="logo_url" class="form__input" value="<?= $val('logo_url') ?>" placeholder="https://... (SVG ou PNG recomendado)" required>
                <span class="form__hint">Prefira SVG ou PNG com fundo transparente. Tamanho ideal: 160×60px.</span>
            </div>

            <?php if ($isEdit && !empty($item['logo_url'])): ?>
            <div class="form__group form-grid__full">
                <label class="form__label">Preview atual</label>
                <div class="logo-preview" style="background: #000; padding: 12px; border-radius: 6px; border: 1px solid var(--line); display: inline-block;">
                    <img src="<?= htmlspecialchars($item['logo_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="max-height: 48px; max-width: 180px; object-fit: contain;">
                </div>
            </div>
            <?php endif; ?>

            <div class="form__group">
                <label class="form__label">Texto alternativo (acessibilidade)</label>
                <input type="text" name="alt_text" class="form__input" value="<?= $val('alt_text') ?>"
                       placeholder="Ex: Logo da Empresa XYZ">
                <span class="form__hint">Deixe em branco para usar o nome da empresa.</span>
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
                <?= $isEdit ? 'Salvar alterações' : 'Criar logo' ?>
            </button>
            <a href="/admin/logos" class="btn btn--secondary">Cancelar</a>
        </div>
    </form>
</div>

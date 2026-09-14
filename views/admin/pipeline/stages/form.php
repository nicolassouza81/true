<?php
$isEdit = $item !== null;
$action = $isEdit ? "/admin/pipeline/stages/{$item['id']}" : '/admin/pipeline/stages';
$old    = $old ?? [];
$val    = fn (string $k, mixed $fallback = '') => htmlspecialchars((string) ($old[$k] ?? $item[$k] ?? $fallback));
$checked = fn (string $k) => !empty($old) ? isset($old[$k]) : !empty($item[$k] ?? false);
?>

<div class="admin-section">
    <div class="admin-section__header">
        <h2><?= htmlspecialchars($title) ?></h2>
        <a href="/admin/pipeline/stages" class="btn btn--secondary btn--sm">
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
                <label class="form__label">Nome da etapa <span class="required">*</span></label>
                <input type="text" name="name" class="form__input" value="<?= $val('name') ?>" placeholder="Ex: Contato feito" required>
            </div>

            <div class="form__group">
                <label class="form__label">Cor</label>
                <input type="color" name="color" class="form__input form__input--color" value="<?= $val('color', '#5b8def') ?>">
            </div>

            <div class="form__group">
                <label class="form__label">Ordem no quadro</label>
                <input type="number" name="sort_order" class="form__input" min="0" value="<?= $val('sort_order', '0') ?>">
                <span class="form__hint">Etapas são exibidas da esquerda para a direita por esta ordem.</span>
            </div>

            <div class="form__group form-grid__full">
                <label class="form__label checkbox-label">
                    <input type="checkbox" name="is_won" value="1" <?= $checked('is_won') ? 'checked' : '' ?>>
                    Etapa de "Ganho" (negócio fechado com sucesso)
                </label>
                <label class="form__label checkbox-label">
                    <input type="checkbox" name="is_lost" value="1" <?= $checked('is_lost') ? 'checked' : '' ?>>
                    Etapa de "Perdido" (negócio encerrado sem sucesso)
                </label>
            </div>
        </div>

        <div class="form__actions">
            <button type="submit" class="btn btn--primary">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <?= $isEdit ? 'Salvar alterações' : 'Criar etapa' ?>
            </button>
            <a href="/admin/pipeline/stages" class="btn btn--secondary">Cancelar</a>
        </div>
    </form>
</div>

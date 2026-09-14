<?php
$isEdit = $item !== null;
$action = $isEdit ? "/admin/pipeline/leads/{$item['id']}" : '/admin/pipeline/leads';
$old    = $old ?? [];
$val    = fn (string $k, mixed $fallback = '') => htmlspecialchars((string) ($old[$k] ?? $item[$k] ?? $fallback));
$currentStageId = (int) ($old['stage_id'] ?? $item['stage_id'] ?? 0);
?>

<div class="admin-section">
    <div class="admin-section__header">
        <h2><?= htmlspecialchars($title) ?></h2>
        <a href="/admin/pipeline" class="btn btn--secondary btn--sm">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Voltar ao funil
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert--error"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>

    <div class="lead-layout">
        <form action="<?= $action ?>" method="POST" class="form form--admin">
            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form__group">
                    <label class="form__label">Nome do lead <span class="required">*</span></label>
                    <input type="text" name="name" class="form__input" value="<?= $val('name') ?>" required>
                </div>

                <div class="form__group">
                    <label class="form__label">Empresa</label>
                    <input type="text" name="company" class="form__input" value="<?= $val('company') ?>">
                </div>

                <div class="form__group">
                    <label class="form__label">E-mail</label>
                    <input type="email" name="email" class="form__input" value="<?= $val('email') ?>">
                </div>

                <div class="form__group">
                    <label class="form__label">Telefone</label>
                    <input type="text" name="phone" class="form__input" value="<?= $val('phone') ?>">
                </div>

                <div class="form__group">
                    <label class="form__label">Etapa <span class="required">*</span></label>
                    <select name="stage_id" class="form__input form__select" required>
                        <?php foreach ($stages as $stage): ?>
                            <option value="<?= (int) $stage['id'] ?>" <?= $currentStageId === (int) $stage['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($stage['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form__group form-grid__full">
                    <label class="form__label">Anotações</label>
                    <textarea name="notes" class="form__input form__textarea" rows="4"><?= $val('notes') ?></textarea>
                </div>
            </div>

            <div class="form__actions">
                <button type="submit" class="btn btn--primary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <?= $isEdit ? 'Salvar alterações' : 'Criar lead' ?>
                </button>
                <a href="/admin/pipeline" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>

        <?php if ($isEdit): ?>
            <!-- Fora do <form> principal: formulários HTML não podem ser aninhados -->
            <form action="/admin/pipeline/leads/<?= (int) $item['id'] ?>" method="POST"
                  onsubmit="return confirm('Excluir este lead? Esta ação não pode ser desfeita.')"
                  class="lead-delete-form" style="margin-top: 1.5rem;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
                <button type="submit" class="btn btn--danger btn--sm">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Excluir lead
                </button>
            </form>
        <?php endif; ?>

        <?php if ($isEdit): ?>
            <aside class="lead-history">
                <h3 class="lead-history__title">Histórico de movimentação</h3>
                <?php if (empty($history)): ?>
                    <p class="text--muted">Nenhuma movimentação registrada ainda.</p>
                <?php else: ?>
                    <ul class="lead-history__list">
                        <?php foreach ($history as $entry): ?>
                            <li class="lead-history__item">
                                <span class="lead-history__date"><?= date('d/m/Y H:i', strtotime($entry['moved_at'])) ?></span>
                                <span class="lead-history__transition">
                                    <?php if ($entry['from_stage_name'] !== null): ?>
                                        <?= htmlspecialchars($entry['from_stage_name']) ?> →
                                    <?php else: ?>
                                        Lead criado em
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($entry['to_stage_name']) ?></strong>
                                </span>
                                <?php if (!empty($entry['moved_by_name'])): ?>
                                    <span class="lead-history__by">por <?= htmlspecialchars($entry['moved_by_name']) ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </aside>
        <?php endif; ?>
    </div>
</div>

<div class="admin-section">
    <div class="admin-section__header">
        <h2>Etapas do funil</h2>
        <div class="u-inline-actions" style="display: flex; gap: 0.75rem;">
            <a href="/admin/pipeline" class="btn btn--secondary btn--sm">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Voltar ao quadro
            </a>
            <a href="/admin/pipeline/stages/create" class="btn btn--primary btn--sm">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nova etapa
            </a>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">Cor</th>
                    <th>Nome</th>
                    <th>Ordem</th>
                    <th>Tipo</th>
                    <th>Leads</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stages as $stage): ?>
                <tr>
                    <td><span class="stage-swatch" style="display: inline-block; width: 18px; height: 18px; border-radius: 4px; background: <?= htmlspecialchars($stage['color']) ?>; border: 1px solid rgba(255,255,255,0.2);"></span></td>
                    <td style="font-weight: 700; color: var(--white);"><?= htmlspecialchars($stage['name']) ?></td>
                    <td><span style="display: inline-block; padding: 2px 8px; background: rgba(255,255,255,0.05); border-radius: 4px; font-weight: 700; font-size: 0.8rem;"><?= (int) $stage['sort_order'] ?></span></td>
                    <td>
                        <?php if ($stage['is_won']): ?>
                            <span class="badge badge--success">Ganho</span>
                        <?php elseif ($stage['is_lost']): ?>
                            <span class="badge badge--danger">Perdido</span>
                        <?php else: ?>
                            <span class="text--muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><strong style="color: var(--white); font-size: 0.95rem;"><?= (int) $stage['lead_count'] ?></strong></td>
                    <td class="table__actions" style="justify-content: flex-end;">
                        <a href="/admin/pipeline/stages/<?= (int) $stage['id'] ?>/edit" class="btn btn--secondary btn--sm" title="Editar">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            Editar
                        </a>
                        <form action="/admin/pipeline/stages/<?= (int) $stage['id'] ?>" method="POST"
                              onsubmit="return confirm('Excluir esta etapa? Só é possível se não houver leads nela.')" style="margin: 0;">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
                            <button type="submit" class="btn btn--danger btn--sm" <?= $stage['lead_count'] > 0 ? 'disabled title="Mova os leads desta etapa antes de excluir"' : 'title="Excluir"' ?>>
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Excluir
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($stages)): ?>
                <tr>
                    <td colspan="6" class="table__empty" style="text-align: center; padding: 2.5rem 1rem; color: var(--muted);">Nenhuma etapa cadastrada ainda. Crie a primeira para começar o funil.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

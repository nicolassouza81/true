<div class="admin-section">
    <div class="admin-section__header">
        <h2>Testemunhos</h2>
        <a href="/admin/testimonials/create" class="btn btn--primary btn--sm">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Novo depoimento
        </a>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Autor</th>
                    <th>Cargo / Empresa</th>
                    <th>Avaliação</th>
                    <th>Ordem</th>
                    <th>Ativo</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testimonials as $t): ?>
                <tr>
                    <td class="text--muted" style="font-weight: 600;"><?= (int) $t['id'] ?></td>
                    <td>
                        <div class="table-author" style="display: flex; align-items: center; gap: 0.75rem;">
                            <?php if (!empty($t['author_avatar'])): ?>
                                <img src="<?= htmlspecialchars($t['author_avatar']) ?>" alt="" class="table-author__avatar" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1px solid var(--line);">
                            <?php else: ?>
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--orange-subtle); color: var(--orange); font-weight: 800; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255, 75, 25, 0.3);">
                                    <?= strtoupper(substr($t['author_name'] ?? 'T', 0, 2)) ?>
                                </div>
                            <?php endif; ?>
                            <span style="font-weight: 700; color: var(--white);"><?= htmlspecialchars($t['author_name']) ?></span>
                        </div>
                    </td>
                    <td class="text--muted" style="font-size: 0.85rem;"><?= htmlspecialchars($t['author_title'] ?? '—') ?></td>
                    <td>
                        <span class="stars stars--sm" style="color: var(--acid); font-size: 0.95rem; letter-spacing: 2px;">
                            <?= str_repeat('★', (int) ($t['rating'] ?? 5)) ?><span style="opacity: 0.25;"><?= str_repeat('★', 5 - (int) ($t['rating'] ?? 5)) ?></span>
                        </span>
                    </td>
                    <td><span style="display: inline-block; padding: 2px 8px; background: rgba(255,255,255,0.05); border-radius: 4px; font-weight: 700; font-size: 0.8rem;"><?= (int) $t['sort_order'] ?></span></td>
                    <td>
                        <span class="badge badge--<?= $t['is_active'] ? 'success' : 'warning' ?>">
                            <?= $t['is_active'] ? 'Sim' : 'Não' ?>
                        </span>
                    </td>
                    <td class="table__actions" style="justify-content: flex-end;">
                        <a href="/admin/testimonials/<?= (int) $t['id'] ?>/edit" class="btn btn--secondary btn--sm" title="Editar">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            Editar
                        </a>
                        <form action="/admin/testimonials/<?= (int) $t['id'] ?>" method="POST"
                              onsubmit="return confirm('Excluir este testemunho?')" style="margin: 0;">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
                            <button type="submit" class="btn btn--danger btn--sm" title="Excluir">
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Excluir
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($testimonials)): ?>
                <tr>
                    <td colspan="7" class="table__empty" style="text-align: center; padding: 2.5rem 1rem; color: var(--muted);">Nenhum testemunho cadastrado.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <nav class="pagination" style="display: flex; gap: 0.5rem; justify-content: center; margin-top: 1.5rem;">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="/admin/testimonials?page=<?= $i ?>"
               class="pagination__item <?= $i === $page ? 'pagination__item--active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>
</div>

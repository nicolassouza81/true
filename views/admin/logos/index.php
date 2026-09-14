<div class="admin-section">
    <div class="admin-section__header">
        <h2>Logos de Parceiros / Clientes</h2>
        <a href="/admin/logos/create" class="btn btn--primary btn--sm">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <span>Novo Logo</span>
        </a>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th style="width:140px;">Logo</th>
                    <th>Nome da Marca</th>
                    <th>Website</th>
                    <th style="width:80px;">Ordem</th>
                    <th style="width:100px;">Status</th>
                    <th style="width:180px;text-align:right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logos as $logo): ?>
                <tr>
                    <td style="color:var(--muted);font-weight:700;"><?= (int) $logo['id'] ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($logo['logo_url']) ?>"
                             alt="<?= htmlspecialchars($logo['name']) ?>"
                             class="logo-thumb"
                             loading="lazy">
                    </td>
                    <td>
                        <strong style="color:var(--white);font-size:0.95rem;"><?= htmlspecialchars($logo['name']) ?></strong>
                        <?php if (!empty($logo['alt_text'])): ?>
                            <br><small style="color:var(--muted);"><?= htmlspecialchars($logo['alt_text']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($logo['website_url'])): ?>
                            <a href="<?= htmlspecialchars($logo['website_url']) ?>" target="_blank" rel="noopener" class="link" style="color:var(--orange);font-weight:600;display:inline-flex;align-items:center;gap:0.3rem;">
                                <span>Visitar</span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                            </a>
                        <?php else: ?>
                            <span style="color:var(--muted-dim);">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:700;color:var(--white);"><?= (int) $logo['sort_order'] ?></td>
                    <td>
                        <span class="badge badge--<?= $logo['is_active'] ? 'success' : 'warning' ?>">
                            <?= $logo['is_active'] ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </td>
                    <td class="table__actions" style="justify-content:flex-end;">
                        <a href="/admin/logos/<?= (int) $logo['id'] ?>/edit" class="btn btn--secondary btn--sm" style="padding:0.35rem 0.75rem;">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                            <span>Editar</span>
                        </a>
                        <form action="/admin/logos/<?= (int) $logo['id'] ?>" method="POST"
                              onsubmit="return confirm('Tem certeza que deseja excluir esta marca?')"
                              style="margin:0;">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
                            <button type="submit" class="btn btn--danger btn--sm" style="padding:0.35rem 0.75rem;">
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                <span>Excluir</span>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($logos)): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:2.5rem;color:var(--muted);">Nenhum logo cadastrado.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-section">
    <div class="admin-section__header">
        <h2>Usuários</h2>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int) $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= (int) $u['role_id'] === 1 ? 'Admin' : 'Cliente' ?></td>
                    <td>
                        <span class="badge badge--<?= $u['status'] === 'active' ? 'success' : 'warning' ?>">
                            <?= htmlspecialchars($u['status']) ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td class="table__actions" style="justify-content: flex-end;">
                        <a href="/admin/users/<?= (int) $u['id'] ?>/edit" class="btn btn--secondary btn--sm" title="Editar">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            Editar
                        </a>

                        <form action="/admin/users/<?= (int) $u['id'] ?>" method="POST" class="u-inline"
                              onsubmit="return confirm('Excluir este usuário?')" style="margin: 0;">
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

                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="table__empty">Nenhum usuário encontrado.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <nav class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="/admin/users?page=<?= $i ?>"
               class="pagination__item <?= $i === $page ? 'pagination__item--active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>
</div>

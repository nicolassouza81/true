<div class="admin-section">
    <div class="admin-section__header">
        <h2>Webhooks</h2>
        <div style="display:flex;gap:var(--space-2)">
            <a href="/admin/webhooks?status=failed"
               class="btn btn--sm <?= $status === 'failed' ? 'btn--primary' : 'btn--secondary' ?>">Falhos</a>
            <a href="/admin/webhooks?status=pending"
               class="btn btn--sm <?= $status === 'pending' ? 'btn--primary' : 'btn--secondary' ?>">Pendentes</a>
            <a href="/admin/webhooks?status=processed"
               class="btn btn--sm <?= $status === 'processed' ? 'btn--primary' : 'btn--secondary' ?>">Processados</a>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Provider</th>
                    <th>Tentativas</th>
                    <th>Último erro</th>
                    <th>Recebido em</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $ev): ?>
                <tr>
                    <td><?= (int) $ev['id'] ?></td>
                    <td><code style="font-size:var(--text-xs)"><?= htmlspecialchars($ev['event_type']) ?></code></td>
                    <td><?= htmlspecialchars($ev['provider']) ?></td>
                    <td><?= (int) $ev['attempts'] ?></td>
                    <td class="text--muted" style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= htmlspecialchars($ev['last_error'] ?? '—') ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($ev['created_at'])) ?></td>
                    <td>
                        <span class="badge badge--<?= match($ev['status']) {
                            'processed' => 'success',
                            'failed'    => 'danger',
                            default     => 'warning',
                        } ?>">
                            <?= htmlspecialchars($ev['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($ev['status'] !== 'processed'): ?>
                        <form action="/admin/webhooks/<?= (int) $ev['id'] ?>/retry" method="POST"
                              onsubmit="return confirm('Reprocessar este evento?')">
                            <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
                            <button type="submit" class="btn btn--ghost btn--sm">Reprocessar</button>
                        </form>
                        <?php else: ?>
                            <span class="text--muted text--sm">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($events)): ?>
                <tr>
                    <td colspan="8" class="table__empty">Nenhum evento com status "<?= htmlspecialchars($status) ?>".</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

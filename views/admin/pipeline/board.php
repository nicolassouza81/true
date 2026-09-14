<div class="admin-section">
    <div class="admin-section__header">
        <h2>Funil de vendas</h2>
        <div class="u-inline-actions" style="display:flex;gap:0.75rem;align-items:center;">
            <a href="/admin/pipeline/stages" class="btn btn--secondary btn--sm">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="21" x2="4" y2="14"></line>
                    <line x1="4" y1="10" x2="4" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12" y2="3"></line>
                    <line x1="20" y1="21" x2="20" y2="16"></line>
                    <line x1="20" y1="12" x2="20" y2="3"></line>
                    <line x1="1" y1="14" x2="7" y2="14"></line>
                    <line x1="9" y1="8" x2="15" y2="8"></line>
                    <line x1="17" y1="16" x2="23" y2="16"></line>
                </svg>
                <span>Gerenciar etapas</span>
            </a>
            <?php if (!empty($stages)): ?>
                <a href="/admin/pipeline/leads/create" class="btn btn--primary btn--sm">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    <span>Novo lead</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($stages)): ?>
        <div class="card" style="padding:2.5rem;text-align:center;">
            <p style="color:var(--muted);margin-bottom:1.5rem;">Nenhuma etapa cadastrada ainda.</p>
            <a href="/admin/pipeline/stages/create" class="btn btn--primary btn--sm">Criar primeira etapa</a>
        </div>
    <?php else: ?>
        <div class="kanban" id="kanbanBoard">
            <?php foreach ($stages as $stage):
                $stageId = (int) $stage['id'];
                $leads   = $columns[$stageId] ?? [];
            ?>
                <div class="kanban__column">
                    <div class="kanban__column-header" style="border-top-color: <?= htmlspecialchars($stage['color']) ?>">
                        <span class="kanban__column-title"><?= htmlspecialchars($stage['name']) ?></span>
                        <span class="kanban__column-count" data-count-for="<?= $stageId ?>"><?= count($leads) ?></span>
                    </div>

                    <div class="kanban__cards" data-stage-id="<?= $stageId ?>">
                        <?php foreach ($leads as $lead): ?>
                            <div class="kanban__card" draggable="true" data-lead-id="<?= (int) $lead['id'] ?>">
                                <strong class="kanban__card-name"><?= htmlspecialchars($lead['name']) ?></strong>
                                <?php if (!empty($lead['company'])): ?>
                                    <span class="kanban__card-company"><?= htmlspecialchars($lead['company']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($lead['email']) || !empty($lead['phone'])): ?>
                                    <span class="kanban__card-meta"><?= htmlspecialchars($lead['phone'] ?? $lead['email'] ?? '') ?></span>
                                <?php endif; ?>
                                <a href="/admin/pipeline/leads/<?= (int) $lead['id'] ?>/edit" class="kanban__card-link">
                                    <span>Ver detalhes</span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <a href="/admin/pipeline/leads/create?stage_id=<?= $stageId ?>" class="kanban__add-card" style="display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Adicionar lead</span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

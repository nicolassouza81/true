<div class="dashboard container" style="max-width: 1400px; margin: 0 auto;">
    <div class="dashboard__header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1.5rem;margin-bottom:2.5rem;border-bottom:1px solid var(--line);padding-bottom:1.5rem;">
        <div>
            <h2 class="dashboard__greeting" style="margin:0;font-family:var(--display);letter-spacing:-0.02em;text-transform:uppercase;color:var(--white);">Olá, <?= htmlspecialchars($user['name'] ?? 'Administrador') ?></h2>
            <p style="margin:0.4rem 0 0;color:var(--muted);font-size:0.95rem;">Painel de gestão operacional de orçamentos e eventos da <strong style="color:var(--white);">TRUE Eventos</strong>.</p>
        </div>
        <div style="display:flex;gap:0.85rem;align-items:center;">
            <a href="/admin/pipeline" class="btn btn--primary">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="5" height="16" rx="1.5"></rect>
                    <rect x="11" y="4" width="5" height="11" rx="1.5"></rect>
                    <rect x="19" y="4" width="5" height="14" rx="1.5"></rect>
                </svg>
                <span>Abrir Funil (Kanban)</span>
            </a>
            <a href="/admin/pipeline/leads/create" class="btn btn--secondary">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span>Novo Lead</span>
            </a>
        </div>
    </div>

    <!-- CARDS DE MÉTRICAS -->
    <div class="dashboard__grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 2.5rem;">
        <div class="card" style="border-left: 4px solid var(--orange); background: var(--surface-card);">
            <p style="margin:0;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);">Total de Leads / Orçamentos</p>
            <h3 class="stat-number" style="color:var(--white);"><?= (int) ($totalLeads ?? 0) ?></h3>
        </div>

        <?php if (!empty($stageCounts)): ?>
            <?php foreach ($stageCounts as $stageId => $stageInfo): ?>
                <div class="card" style="border-left: 4px solid <?= htmlspecialchars($stageInfo['color']) ?>; background: var(--surface-card);">
                    <p style="margin:0;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);"><?= htmlspecialchars($stageInfo['name']) ?></p>
                    <h3 class="stat-number" style="color:var(--white);"><?= (int) $stageInfo['count'] ?></h3>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="dashboard__grid" style="grid-template-columns: 2.2fr 1fr; gap: 1.8rem; align-items: start;">
        <!-- ÚLTIMOS LEADS RECEBIDOS -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;border-bottom:1px solid var(--line);padding-bottom:1rem;">
                <h3 class="card__title" style="margin:0;">Últimas Solicitações de Orçamento</h3>
                <a href="/admin/pipeline" class="btn btn--ghost btn--sm" style="color:var(--orange);font-weight:700;">
                    Ver Kanban 
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>

            <?php if (!empty($recentLeads)): ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome / Contato</th>
                                <th>Empresa</th>
                                <th>Data</th>
                                <th style="text-align:right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLeads as $lead): ?>
                                <tr>
                                    <td>
                                        <strong style="color:var(--white);font-size:0.95rem;"><?= htmlspecialchars($lead['name']) ?></strong><br>
                                        <small style="color:var(--muted);"><?= htmlspecialchars($lead['phone'] ?? $lead['email'] ?? '—') ?></small>
                                    </td>
                                    <td><span style="color:var(--paper);"><?= htmlspecialchars($lead['company'] ?? '—') ?></span></td>
                                    <td><span style="color:var(--muted);font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?></span></td>
                                    <td style="text-align:right;">
                                        <a href="/admin/pipeline/leads/<?= (int) $lead['id'] ?>/edit" class="btn btn--ghost btn--sm" style="color:var(--orange);border-color:var(--line);">
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color:var(--muted);padding:1.5rem 0;">Nenhum orçamento recebido ainda. Quando clientes solicitarem orçamentos no site, eles aparecerão aqui automaticamente.</p>
            <?php endif; ?>
        </div>

        <!-- ATALHOS E DADOS DO ADMINISTRADOR -->
        <div style="display:flex;flex-direction:column;gap:1.8rem;">
            <div class="card">
                <h3 class="card__title" style="border-bottom:1px solid var(--line);padding-bottom:0.75rem;">Atalhos de Gestão</h3>
                <div style="display:flex;flex-direction:column;gap:0.6rem;margin-top:1rem;">
                    <a href="/admin/pipeline" class="btn btn--secondary btn--full" style="justify-content:flex-start;">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="5" height="16" rx="1.5"></rect>
                            <rect x="11" y="4" width="5" height="11" rx="1.5"></rect>
                            <rect x="19" y="4" width="5" height="14" rx="1.5"></rect>
                        </svg>
                        <span>Funil de Vendas (Kanban)</span>
                    </a>
                    <a href="/admin/pipeline/stages" class="btn btn--secondary btn--full" style="justify-content:flex-start;">
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
                        <span>Gerenciar Etapas do Funil</span>
                    </a>
                    <a href="/admin/testimonials" class="btn btn--secondary btn--full" style="justify-content:flex-start;">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Gerenciar Depoimentos</span>
                    </a>
                    <a href="/admin/logos" class="btn btn--secondary btn--full" style="justify-content:flex-start;">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                        <span>Logos de Parceiros / Clientes</span>
                    </a>
                    <a href="/admin/users" class="btn btn--secondary btn--full" style="justify-content:flex-start;">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Equipe e Usuários</span>
                    </a>
                </div>
            </div>

            <div class="card">
                <h3 class="card__title" style="border-bottom:1px solid var(--line);padding-bottom:0.75rem;">Conta do Gestor</h3>
                <dl class="data-list" style="margin:1rem 0 0;">
                    <dt>Nome do Gestor</dt>
                    <dd><?= htmlspecialchars($user['name'] ?? '') ?></dd>
                    <dt>E-mail Corporativo</dt>
                    <dd><?= htmlspecialchars($user['email'] ?? '') ?></dd>
                </dl>
                <div style="margin-top:1.2rem;border-top:1px solid var(--line);padding-top:1rem;">
                    <a href="/profile" class="btn btn--ghost btn--sm" style="color:var(--paper);border-color:var(--line);">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                        <span>Editar Perfil / Senha</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

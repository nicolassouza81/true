/**
 * pipeline.js — Drag-and-drop do quadro Kanban do funil de vendas
 * (/admin/pipeline). Usa a API nativa HTML5 de drag-and-drop (sem lib
 * externa, consistente com o resto do projeto).
 *
 * Movimento é feito com UI otimista: o card muda de coluna na hora e só
 * é desfeito (com reload) se o servidor rejeitar a movimentação.
 */

export function initPipelineBoard() {
    const board = document.getElementById('kanbanBoard');

    if (!board) {
        return;
    }

    // Token CSRF atual. O CsrfMiddleware rotaciona o token a cada POST
    // válido, então guardamos localmente e atualizamos com o valor que o
    // endpoint /move devolve — assim dá pra arrastar vários cards seguidos
    // sem recarregar a página.
    let csrfToken = document.body.dataset.csrf || '';

    let draggedCard = null;

    board.querySelectorAll('.kanban__card').forEach((card) => {
        card.addEventListener('dragstart', (e) => {
            draggedCard = card;
            card.classList.add('kanban__card--dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.leadId);
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('kanban__card--dragging');
            draggedCard = null;
        });
    });

    board.querySelectorAll('.kanban__cards').forEach((column) => {
        column.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            column.classList.add('kanban__cards--dragover');
        });

        column.addEventListener('dragleave', () => {
            column.classList.remove('kanban__cards--dragover');
        });

        column.addEventListener('drop', (e) => {
            e.preventDefault();
            column.classList.remove('kanban__cards--dragover');

            if (!draggedCard) {
                return;
            }

            const leadId = draggedCard.dataset.leadId;
            const fromColumn = draggedCard.closest('.kanban__cards');
            const toStageId = column.dataset.stageId;

            if (fromColumn === column) {
                return; // sem reordenação dentro da mesma coluna por enquanto
            }

            // UI otimista: move o card já na tela
            column.appendChild(draggedCard);
            updateColumnCounts(fromColumn, column);

            moveLead(leadId, toStageId, csrfToken)
                .then((data) => {
                    if (data && data.csrf) {
                        csrfToken = data.csrf;
                    }
                })
                .catch((err) => {
                    console.error('Falha ao mover lead:', err);
                    alert('Não foi possível mover o lead. A página será recarregada.');
                    window.location.reload();
                });
        });
    });

    function updateColumnCounts(fromColumn, toColumn) {
        [fromColumn, toColumn].forEach((col) => {
            const stageId = col.dataset.stageId;
            const counter = board.querySelector(`[data-count-for="${stageId}"]`);
            if (counter) {
                counter.textContent = String(col.querySelectorAll('.kanban__card').length);
            }
        });
    }

    async function moveLead(leadId, stageId, token) {
        const response = await fetch(`/admin/pipeline/leads/${leadId}/move`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': token,
            },
            body: JSON.stringify({ stage_id: stageId }),
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }
}

-- Etapas do funil de vendas (Kanban). Editáveis pelo admin em /admin/pipeline/stages.
-- is_won / is_lost marcam etapas terminais (ex: "Ganho" / "Perdido") para relatórios futuros.
CREATE TABLE IF NOT EXISTS pipeline_stages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#5b8def',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    is_won TINYINT(1) NOT NULL DEFAULT 0,
    is_lost TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pipeline_stages_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cards do funil (leads/oportunidades comerciais).
-- ON DELETE RESTRICT em stage_id: uma etapa com leads não pode ser excluída
-- sem antes mover ou remover os leads (ver PipelineStageController::destroy).
CREATE TABLE IF NOT EXISTS leads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stage_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    company VARCHAR(150) NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    notes TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_leads_stage FOREIGN KEY (stage_id) REFERENCES pipeline_stages(id) ON DELETE RESTRICT,
    INDEX idx_leads_stage (stage_id),
    INDEX idx_leads_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

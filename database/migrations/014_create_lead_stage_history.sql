-- Histórico de movimentação de cada lead entre etapas do funil (auditoria do pipeline).
CREATE TABLE IF NOT EXISTS lead_stage_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id INT UNSIGNED NOT NULL,
    from_stage_id INT UNSIGNED NULL,
    to_stage_id INT UNSIGNED NOT NULL,
    moved_by INT UNSIGNED NULL,
    moved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lsh_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    CONSTRAINT fk_lsh_from_stage FOREIGN KEY (from_stage_id) REFERENCES pipeline_stages(id) ON DELETE SET NULL,
    CONSTRAINT fk_lsh_to_stage FOREIGN KEY (to_stage_id) REFERENCES pipeline_stages(id) ON DELETE CASCADE,
    CONSTRAINT fk_lsh_user FOREIGN KEY (moved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_lsh_lead (lead_id, moved_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

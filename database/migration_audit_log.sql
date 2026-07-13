-- Audit log table for non-repudiation compliance
-- Immutable: no UPDATE or DELETE operations allowed (application-level constraint)

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id INT UNSIGNED DEFAULT NULL,
    user_type VARCHAR(20) NOT NULL DEFAULT 'funcionario',
    username VARCHAR(100) DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500) NOT NULL DEFAULT '',
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(50) DEFAULT NULL,
    details JSON DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_audit_created (created_at),
    INDEX idx_audit_user (user_id, user_type),
    INDEX idx_audit_action (action),
    INDEX idx_audit_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Prevent application-level DELETE on audit_log
-- (MySQL doesn't support SQL-level DML restrictions, so this is enforced in AuditLogger)

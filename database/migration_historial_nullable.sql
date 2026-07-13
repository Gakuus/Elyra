-- Make historial_estado.traslado_id nullable so records survive parent deletion
-- This ensures the audit trail is preserved when a traslado is deleted

ALTER TABLE historial_estado
    MODIFY COLUMN traslado_id INT UNSIGNED DEFAULT NULL;

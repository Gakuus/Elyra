-- Agregar roles de hospital al ENUM de funcionario.rol
-- Los roles de conductor/copiloto se mantienen pero se gestionan desde su propia sección

ALTER TABLE funcionario
    MODIFY COLUMN rol ENUM(
        'superadmin', 'admin', 'medico', 'enfermero',
        'tecnico', 'recepcionista', 'farmaceutico',
        'conductor', 'copiloto'
    ) NOT NULL;

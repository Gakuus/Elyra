-- Test data for BDD features
-- Run after main seed: mysql -u elyra -p elyra < database/seeds/test_data.sql

-- Admin should already exist from seed.php (username=admin, password=admin)

-- Create a test paciente linked to usuario
INSERT INTO usuario (tipo, nombre, apellido, email, documento_identidad)
SELECT 'paciente', 'Juan', 'Pérez', 'juan@test.com', '12345678'
WHERE NOT EXISTS (SELECT 1 FROM paciente WHERE id IN (SELECT id FROM usuario WHERE email = 'juan@test.com'));

INSERT INTO paciente (id, token_acceso, username, password_hash, telefono, activo)
SELECT u.id, 'test-token-juan-perez-123', 'jpaciente', '$2y$12$HcCnoYt9iGy7ZjMB0olhA.9/hCW9e3CrxkYckHQaCCxQrHVNknErK', '099123456', TRUE
FROM usuario u WHERE u.email = 'juan@test.com'
AND NOT EXISTS (SELECT 1 FROM paciente p WHERE p.id = u.id);

-- Create a test conductor funcionario
INSERT INTO usuario (tipo, nombre, apellido, email)
SELECT 'funcionario', 'Carlos', 'Rodríguez', 'carlos@test.com'
WHERE NOT EXISTS (SELECT 1 FROM funcionario WHERE username = 'crodriguez');

INSERT INTO funcionario (id, username, password_hash, licencia, telefono, activo, rol)
SELECT u.id, 'crodriguez', '$2y$12$HcCnoYt9iGy7ZjMB0olhA.9/hCW9e3CrxkYckHQaCCxQrHVNknErK', 'LIC-001', '098765432', TRUE, 'conductor'
FROM usuario u WHERE u.email = 'carlos@test.com'
AND NOT EXISTS (SELECT 1 FROM funcionario f WHERE f.username = 'crodriguez');

-- Get IDs for reference
SET @admin_id = (SELECT id FROM funcionario WHERE username = 'admin' LIMIT 1);
SET @paciente_id = (SELECT id FROM paciente WHERE username = 'jpaciente' LIMIT 1);
SET @conductor_id = (SELECT id FROM funcionario WHERE username = 'crodriguez' LIMIT 1);
SET @categoria_id = (SELECT id FROM categoria WHERE tipo = 'tipo_documento' AND nombre = 'Protocolos' LIMIT 1);
SET @especialidad_id = (SELECT id FROM categoria WHERE tipo = 'especialidad' AND nombre = 'Cardiología' LIMIT 1);

-- Create test documents
INSERT INTO documento (titulo, archivo_path, archivo_nombre, categoria_id, especialidad_id, subido_por, paciente_id, activo, created_at)
SELECT 'Indicaciones post-operatorias', '/tmp/test-doc-1.pdf', 'indicaciones_post_1.pdf', @categoria_id, @especialidad_id, @admin_id, @paciente_id, TRUE, NOW()
WHERE NOT EXISTS (SELECT 1 FROM documento WHERE titulo = 'Indicaciones post-operatorias' AND subido_por = @admin_id);

INSERT INTO documento (titulo, archivo_path, archivo_nombre, categoria_id, especialidad_id, subido_por, activo, created_at)
SELECT 'Protocolo de emergencia', '/tmp/test-doc-2.pdf', 'protocolo_emergencia_1.pdf', @categoria_id, @especialidad_id, @admin_id, TRUE, NOW()
WHERE NOT EXISTS (SELECT 1 FROM documento WHERE titulo = 'Protocolo de emergencia' AND subido_por = @admin_id);

-- Create a test vehiculo
INSERT INTO vehiculo (patente, modelo, anio)
SELECT 'ABC-1234', 'Toyota Hiace', 2023
WHERE NOT EXISTS (SELECT 1 FROM vehiculo WHERE patente = 'ABC-1234');

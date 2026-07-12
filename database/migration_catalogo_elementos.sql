-- =============================================================
-- Migración: Catálogo de elementos para traslados
-- Agrega tabla catalogo_elemento para insumos, equipamiento y órganos
-- =============================================================

CREATE TABLE IF NOT EXISTS catalogo_elemento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('insumo', 'equipamiento', 'organo') NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_catalogo_tipo_nombre (tipo, nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insumos médicos comunes
INSERT INTO catalogo_elemento (tipo, nombre, descripcion) VALUES
('insumo', 'Jeringa 5ml', 'Jeringa descartable de 5 mililitros'),
('insumo', 'Jeringa 10ml', 'Jeringa descartable de 10 mililitros'),
('insumo', 'Gasa estéril', 'Gasa estéril para curaciones'),
('insumo', 'Barra de catéter', 'Catéter intravenoso 18G'),
('insumo', 'Sueroterapia 500ml', 'Solución fisiológica 500ml'),
('insumo', 'Sueroterapia 1000ml', 'Solución fisiológica 1000ml'),
('insumo', 'Guantes descartables', 'Guantes de látex talla M'),
('insumo', 'Mascarilla N95', 'Mascarilla de protección respiratoria N95'),
('insumo', 'Venda elástica', 'Venda elástica de 10cm'),
('insumo', 'Esparadrapo', 'Rollo de esparadrapo 2.5cm'),
('insumo', 'Oxígeno suplementario', 'Balde de oxígeno portátil'),
('insumo', 'Bolsa de reanimación', 'Ambú talla adulto'),
('insumo', 'Tijera de emergencia', 'Tijera para corte de ropa'),
('insumo', 'Laringoscopio', 'Equipo de intubación portátil'),
('insumo', 'Sonido nasogástrico', 'Sonda NG calibre 16'),
('insumo', 'Pañales descartables', 'Pañales para adulto talla M'),
('insumo', 'Toallitas antisépticas', 'Toallitas con clorhexidina'),
('insumo', 'Cinta adhesiva médica', 'Rollo de cinta hipoalergénica'),
('insumo', 'Termómetro digital', 'Termómetro digital clínico'),
('insumo', 'Oxímetro de pulso', 'Pulsioxímetro digital');

-- Equipamiento médico
INSERT INTO catalogo_elemento (tipo, nombre, descripcion) VALUES
('equipamiento', 'Desfibrilador externo', 'DEA semiautomático'),
('equipamiento', 'Monitor multiparamétrico', 'Monitor de signos vitales portátil'),
('equipamiento', 'Camilla plegable', 'Camilla de transporte plegable'),
('equipamiento', 'Silla de ruedas', 'Silla de ruedas estándar'),
('equipamiento', 'Oximetría portátil', 'Concentrador de oxígeno portátil'),
('equipamiento', 'Aspirador portátil', 'Aspirador de secreciones portátil'),
('equipamiento', 'Equipo de inmovilización', 'Collar cervical + tabla espinal'),
('equipamiento', 'Linterna oftalmoscópica', 'Linterna clínica con oftalmoscopio'),
('equipamiento', 'Estetoscopio', 'Estetoscopio de doble uso'),
('equipamiento', 'Esfigmomanómetro', 'Tensiómetro aneroide con manguito');

-- Órganos para trasplante
INSERT INTO catalogo_elemento (tipo, nombre, descripcion) VALUES
('organo', 'Riñón', 'Riñón para trasplante'),
('organo', 'Hígado', 'Hígado para trasplante'),
('organo', 'Corazón', 'Corazón para trasplante'),
('organo', 'Pulmón', 'Pulmón para trasplante'),
('organo', 'Páncreas', 'Páncreas para trasplante'),
('organo', 'Córnea', 'Córnea para trasplante'),
('organo', 'Médula ósea', 'Médula ósea para trasplante');

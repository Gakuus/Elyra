-- =============================================================
-- ELYRA — Hospital de Clínicas
-- Esquema de Base de Datos MySQL
-- Versión: 7.1 (índices inline, sin CREATE INDEX standalone)
-- Migración desde 6.2:
--   ALTER TABLE usuario ADD COLUMN reset_token VARCHAR(64) NULL AFTER foto;
--   ALTER TABLE usuario ADD COLUMN reset_token_expires_at DATETIME NULL AFTER reset_token;
-- =============================================================

CREATE DATABASE IF NOT EXISTS elyra
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE elyra;

-- =============================================================
-- MODULO DE IDENTIDAD
-- Herencia: USUARIO (base) → FUNCIONARIO / PACIENTE
--   - funcionario: empleado con login (admin, superadmin, conductor)
--   - paciente: persona que viaja en ambulancia o accede a docs vía QR
-- =============================================================

CREATE TABLE IF NOT EXISTS usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('funcionario', 'paciente') NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    documento_identidad VARCHAR(20) UNIQUE,
    foto LONGBLOB NULL,
    reset_token VARCHAR(64) NULL,
    reset_token_expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS funcionario (
    id INT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255),
    licencia VARCHAR(50),
    licencia_conducir VARCHAR(50),
    telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    rol ENUM('superadmin', 'admin', 'medico', 'enfermero', 'tecnico', 'recepcionista', 'farmaceutico', 'conductor', 'copiloto'),
    INDEX idx_funcionario_rol (rol),
    INDEX idx_funcionario_activo (activo),
    FOREIGN KEY (id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS codigo_qr (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS paciente (
    id INT PRIMARY KEY,
    token_acceso VARCHAR(64) UNIQUE,
    username VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255),
    telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    codigo_qr_id INT NULL,
    INDEX idx_paciente_qr (codigo_qr_id),
    FOREIGN KEY (id) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (codigo_qr_id) REFERENCES codigo_qr(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- MODULO DE DOCUMENTACION
-- =============================================================

CREATE TABLE IF NOT EXISTS categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    tipo ENUM('especialidad', 'tipo_documento') NOT NULL DEFAULT 'tipo_documento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS encuesta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE,
    creada_por INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_encuesta_activa (activa),
    INDEX idx_encuesta_creada_por (creada_por),
    FOREIGN KEY (creada_por) REFERENCES funcionario(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pregunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encuesta_id INT NOT NULL,
    tipo ENUM('multiple_choice', 'escala', 'texto_libre') NOT NULL,
    texto VARCHAR(500) NOT NULL,
    opciones JSON,
    requerida BOOLEAN DEFAULT TRUE,
    orden INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pregunta_encuesta (encuesta_id),
    FOREIGN KEY (encuesta_id) REFERENCES encuesta(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    archivo_path VARCHAR(255) NOT NULL,
    archivo_nombre VARCHAR(100) NOT NULL,
    archivo_contenido LONGBLOB NULL,
    codigo_qr_id INT NULL,
    qr_path VARCHAR(255),
    categoria_id INT NOT NULL,
    especialidad_id INT NULL,
    encuesta_id INT NULL UNIQUE,
    paciente_id INT NULL,
    subido_por INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_documento_categoria (categoria_id),
    INDEX idx_documento_activo (activo),
    INDEX idx_documento_qr (codigo_qr_id),
    INDEX idx_documento_paciente (paciente_id),
    FOREIGN KEY (codigo_qr_id) REFERENCES codigo_qr(id) ON DELETE SET NULL,
    FOREIGN KEY (categoria_id) REFERENCES categoria(id) ON DELETE RESTRICT,
    FOREIGN KEY (especialidad_id) REFERENCES categoria(id) ON DELETE SET NULL,
    FOREIGN KEY (encuesta_id) REFERENCES encuesta(id) ON DELETE SET NULL,
    FOREIGN KEY (paciente_id) REFERENCES paciente(id) ON DELETE SET NULL,
    FOREIGN KEY (subido_por) REFERENCES funcionario(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS respuesta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sesion_token VARCHAR(64) NOT NULL,
    encuesta_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    token_paciente VARCHAR(64),
    valor_opcion INT NULL,
    valor_texto TEXT NULL,
    valor_numerico INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_respuesta_sesion_pregunta (sesion_token, pregunta_id),
    INDEX idx_respuesta_sesion (sesion_token),
    INDEX idx_respuesta_pregunta (pregunta_id),
    FOREIGN KEY (encuesta_id) REFERENCES encuesta(id) ON DELETE CASCADE,
    FOREIGN KEY (pregunta_id) REFERENCES pregunta(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- MODULO DE AMBULANCIAS
-- =============================================================

CREATE TABLE IF NOT EXISTS vehiculo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patente VARCHAR(20) NOT NULL UNIQUE,
    modelo VARCHAR(100),
    anio YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ruta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    origen VARCHAR(200) NOT NULL,
    destino VARCHAR(200) NOT NULL,
    distancia_km DECIMAL(10,2),
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS traslado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    conductor_id INT NOT NULL,
    copiloto_id INT NULL,
    vehiculo_id INT NULL,
    ruta_id INT NULL,
    origen VARCHAR(200) NOT NULL,
    origen_lat DECIMAL(10, 7) NULL,
    origen_lng DECIMAL(10, 7) NULL,
    destino VARCHAR(200) NOT NULL,
    destino_lat DECIMAL(10, 7) NULL,
    destino_lng DECIMAL(10, 7) NULL,
    hora_salida_estimada DATETIME,
    hora_salida_efectiva DATETIME,
    hora_llegada_destino DATETIME,
    hora_inicio_retorno DATETIME,
    hora_llegada_hospital DATETIME,
    estado ENUM('pendiente', 'en_curso', 'en_destino', 'en_retorno', 'completado', 'cancelado') DEFAULT 'pendiente',
    motivo_cancelacion TEXT NULL,
    registrado_por INT NOT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_traslado_estado (estado),
    INDEX idx_traslado_conductor (conductor_id),
    INDEX idx_traslado_vehiculo (vehiculo_id),
    INDEX idx_traslado_fecha (created_at),
    FOREIGN KEY (conductor_id) REFERENCES funcionario(id) ON DELETE RESTRICT,
    FOREIGN KEY (copiloto_id) REFERENCES funcionario(id) ON DELETE SET NULL,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculo(id) ON DELETE SET NULL,
    FOREIGN KEY (ruta_id) REFERENCES ruta(id) ON DELETE SET NULL,
    FOREIGN KEY (registrado_por) REFERENCES funcionario(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS elemento_traslado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    traslado_id INT NOT NULL,
    tipo ENUM('paciente', 'organo', 'equipamiento', 'insumo') NOT NULL,
    paciente_id INT NULL,
    descripcion VARCHAR(255),
    cantidad INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_elemento_traslado (traslado_id),
    INDEX idx_elemento_paciente (paciente_id),
    FOREIGN KEY (traslado_id) REFERENCES traslado(id) ON DELETE CASCADE,
    FOREIGN KEY (paciente_id) REFERENCES paciente(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS historial_estado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    traslado_id INT NULL,
    estado_anterior VARCHAR(20),
    estado_nuevo VARCHAR(20) NOT NULL,
    observacion TEXT,
    actualizado_por INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_historial_timeline (traslado_id, created_at),
    FOREIGN KEY (traslado_id) REFERENCES traslado(id) ON DELETE SET NULL,
    FOREIGN KEY (actualizado_por) REFERENCES funcionario(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- MODULO GPS / UBICACION EN TIEMPO REAL
-- =============================================================

CREATE TABLE IF NOT EXISTS ubicacion_conductor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conductor_id INT NOT NULL,
    traslado_id INT NULL,
    latitud DECIMAL(10, 7) NOT NULL,
    longitud DECIMAL(10, 7) NOT NULL,
    heading SMALLINT NULL,
    velocidad DECIMAL(5, 1) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_conductor (conductor_id),
    FOREIGN KEY (conductor_id) REFERENCES funcionario(id) ON DELETE CASCADE,
    FOREIGN KEY (traslado_id) REFERENCES traslado(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS historial_ubicacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conductor_id INT NOT NULL,
    traslado_id INT NULL,
    latitud DECIMAL(10, 7) NOT NULL,
    longitud DECIMAL(10, 7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_hist_conductor (conductor_id, created_at),
    INDEX idx_hist_traslado (traslado_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- AUDITORIA INMUTABLE
-- =============================================================

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT NULL,
    user_type VARCHAR(20) NULL,
    username VARCHAR(50) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id VARCHAR(50) NULL,
    details JSON NULL,
    INDEX idx_audit_created (created_at),
    INDEX idx_audit_user (user_id, user_type),
    INDEX idx_audit_action (action),
    INDEX idx_audit_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- CATÁLOGO DE ELEMENTOS PARA TRASLADOS
-- =============================================================

CREATE TABLE IF NOT EXISTS catalogo_elemento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('insumo', 'equipamiento', 'organo') NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_catalogo_tipo_nombre (tipo, nombre),
    INDEX idx_catalogo_tipo (tipo),
    INDEX idx_catalogo_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- MODULO DE NOTICIAS
-- =============================================================

CREATE TABLE IF NOT EXISTS noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    imagen VARCHAR(255) NULL,
    autor_id INT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_noticias_activo (activo),
    INDEX idx_noticias_autor (autor_id),
    INDEX idx_noticias_created (created_at),
    FOREIGN KEY (autor_id) REFERENCES usuario(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

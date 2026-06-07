-- =============================================================
-- ELYRA — Hospital de Clínicas
-- Esquema de Base de Datos MySQL
-- Versión: 5.0 (simplificado: 11 tablas)
-- =============================================================

CREATE DATABASE IF NOT EXISTS elyra
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE elyra;

-- =============================================================
-- TABLA DE IDENTIDAD (single-table inheritance)
-- tipo = 'funcionario' → tiene username, password, licencia, rol
-- tipo = 'paciente'    → tiene token_acceso (sin autenticación)
-- =============================================================

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('funcionario', 'paciente') NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    username VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255),
    documento_identidad VARCHAR(20) UNIQUE,
    licencia VARCHAR(50),
    telefono VARCHAR(20),
    token_acceso VARCHAR(64) UNIQUE,
    activo BOOLEAN DEFAULT TRUE,
    rol ENUM('admin', 'superadmin', 'conductor'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- MODULO DE DOCUMENTACION
-- =============================================================

CREATE TABLE categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE encuesta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE,
    creada_por INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creada_por) REFERENCES usuario(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pregunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encuesta_id INT NOT NULL,
    tipo ENUM('multiple_choice', 'escala', 'texto_libre') NOT NULL,
    texto VARCHAR(500) NOT NULL,
    opciones JSON,
    requerida BOOLEAN DEFAULT TRUE,
    orden INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (encuesta_id) REFERENCES encuesta(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    archivo_path VARCHAR(255) NOT NULL,
    archivo_nombre VARCHAR(100) NOT NULL,
    qr_codigo VARCHAR(64) NOT NULL UNIQUE,
    qr_path VARCHAR(255),
    categoria_id INT NOT NULL,
    encuesta_id INT NULL UNIQUE,
    subido_por INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categoria(id) ON DELETE RESTRICT,
    FOREIGN KEY (encuesta_id) REFERENCES encuesta(id) ON DELETE SET NULL,
    FOREIGN KEY (subido_por) REFERENCES usuario(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE respuesta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sesion_token VARCHAR(64) NOT NULL,
    encuesta_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    token_paciente VARCHAR(64),
    valor_opcion INT NULL,
    valor_texto TEXT NULL,
    valor_numerico INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (encuesta_id) REFERENCES encuesta(id) ON DELETE CASCADE,
    FOREIGN KEY (pregunta_id) REFERENCES pregunta(id) ON DELETE CASCADE,
    UNIQUE KEY uk_respuesta_sesion_pregunta (sesion_token, pregunta_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- MODULO DE AMBULANCIAS
-- =============================================================

CREATE TABLE vehiculo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patente VARCHAR(20) NOT NULL UNIQUE,
    modelo VARCHAR(100),
    anio YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ruta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    origen VARCHAR(200) NOT NULL,
    destino VARCHAR(200) NOT NULL,
    distancia_km DECIMAL(10,2),
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- Entidad principal
-- =============================================================

CREATE TABLE traslado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    conductor_id INT NOT NULL,
    copiloto_id INT NULL,
    vehiculo_id INT NULL,
    ruta_id INT NULL,
    origen VARCHAR(200) NOT NULL,
    destino VARCHAR(200) NOT NULL,
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
    FOREIGN KEY (conductor_id) REFERENCES usuario(id) ON DELETE RESTRICT,
    FOREIGN KEY (copiloto_id) REFERENCES usuario(id) ON DELETE SET NULL,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculo(id) ON DELETE SET NULL,
    FOREIGN KEY (ruta_id) REFERENCES ruta(id) ON DELETE SET NULL,
    FOREIGN KEY (registrado_por) REFERENCES usuario(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- Tabla polimorfica de elementos trasladados
-- tipo discrimina: paciente / organo / equipamiento / insumo
-- paciente_id solo se usa cuando tipo = 'paciente'
-- descripcion se usa para organo, equipamiento e insumo
-- =============================================================

CREATE TABLE elemento_traslado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    traslado_id INT NOT NULL,
    tipo ENUM('paciente', 'organo', 'equipamiento', 'insumo') NOT NULL,
    paciente_id INT NULL,
    descripcion VARCHAR(255),
    cantidad INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (traslado_id) REFERENCES traslado(id) ON DELETE CASCADE,
    FOREIGN KEY (paciente_id) REFERENCES usuario(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- Trazabilidad
-- =============================================================

CREATE TABLE historial_estado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    traslado_id INT NOT NULL,
    estado_anterior VARCHAR(20),
    estado_nuevo VARCHAR(20) NOT NULL,
    observacion TEXT,
    actualizado_por INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (traslado_id) REFERENCES traslado(id) ON DELETE CASCADE,
    FOREIGN KEY (actualizado_por) REFERENCES usuario(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- INDICES
-- =============================================================

-- Documentacion
CREATE INDEX idx_documento_categoria ON documento(categoria_id);
CREATE INDEX idx_documento_activo ON documento(activo);
CREATE INDEX idx_documento_qr ON documento(qr_codigo);
CREATE INDEX idx_encuesta_activa ON encuesta(activa);
CREATE INDEX idx_encuesta_creada_por ON encuesta(creada_por);
CREATE INDEX idx_pregunta_encuesta ON pregunta(encuesta_id);
CREATE INDEX idx_respuesta_sesion ON respuesta(sesion_token);
CREATE INDEX idx_respuesta_pregunta ON respuesta(pregunta_id);

-- Ambulancias
CREATE INDEX idx_traslado_estado ON traslado(estado);
CREATE INDEX idx_traslado_conductor ON traslado(conductor_id);
CREATE INDEX idx_traslado_vehiculo ON traslado(vehiculo_id);
CREATE INDEX idx_traslado_fecha ON traslado(created_at);
CREATE INDEX idx_traslado_codigo ON traslado(codigo);
CREATE INDEX idx_elemento_traslado ON elemento_traslado(traslado_id);
CREATE INDEX idx_elemento_paciente ON elemento_traslado(paciente_id);
CREATE INDEX idx_historial_timeline ON historial_estado(traslado_id, created_at);

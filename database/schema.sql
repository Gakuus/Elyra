-- =============================================================
-- ELYRA — Hospital de Clínicas
-- Esquema de Base de Datos MySQL
-- Versión: 1.0
-- =============================================================

CREATE DATABASE IF NOT EXISTS elyra
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE elyra;

-- =============================================================
-- TABLAS GLOBALES
-- =============================================================

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    rol ENUM('admin', 'superadmin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- MÓDULO DE DOCUMENTACIÓN
-- =============================================================

CREATE TABLE categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
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
    subido_por INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categoria(id),
    FOREIGN KEY (subido_por) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE encuesta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE,
    creada_por INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creada_por) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pregunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encuesta_id INT NOT NULL,
    tipo ENUM('multiple_choice', 'escala', 'texto_libre') NOT NULL,
    texto VARCHAR(500) NOT NULL,
    requerida BOOLEAN DEFAULT TRUE,
    orden INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (encuesta_id) REFERENCES encuesta(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE opcion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    texto VARCHAR(255) NOT NULL,
    valor INT,
    orden INT NOT NULL,
    FOREIGN KEY (pregunta_id) REFERENCES pregunta(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE respuesta_encuesta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encuesta_id INT NOT NULL,
    token_paciente VARCHAR(64),
    completada BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (encuesta_id) REFERENCES encuesta(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE respuesta_pregunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    respuesta_encuesta_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    opcion_id INT NULL,
    valor_texto TEXT NULL,
    valor_numerico INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (respuesta_encuesta_id) REFERENCES respuesta_encuesta(id) ON DELETE CASCADE,
    FOREIGN KEY (pregunta_id) REFERENCES pregunta(id),
    FOREIGN KEY (opcion_id) REFERENCES opcion(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE encuesta_documento (
    encuesta_id INT NOT NULL,
    documento_id INT NOT NULL,
    PRIMARY KEY (encuesta_id, documento_id),
    FOREIGN KEY (encuesta_id) REFERENCES encuesta(id) ON DELETE CASCADE,
    FOREIGN KEY (documento_id) REFERENCES documento(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- MÓDULO DE TRAZABILIDAD DE AMBULANCIAS
-- =============================================================

CREATE TABLE conductor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    documento_identidad VARCHAR(20) UNIQUE,
    licencia VARCHAR(50),
    telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
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

CREATE TABLE traslado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    conductor_id INT NOT NULL,
    copiloto_nombre VARCHAR(150),
    elemento_trasladado VARCHAR(200) NOT NULL,
    tipo_elemento ENUM('paciente', 'equipamiento', 'insumo') NOT NULL,
    origen VARCHAR(200) NOT NULL,
    destino VARCHAR(200) NOT NULL,
    ruta_id INT NULL,
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
    FOREIGN KEY (conductor_id) REFERENCES conductor(id),
    FOREIGN KEY (ruta_id) REFERENCES ruta(id),
    FOREIGN KEY (registrado_por) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE historial_estado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    traslado_id INT NOT NULL,
    estado_anterior VARCHAR(20),
    estado_nuevo VARCHAR(20) NOT NULL,
    observacion TEXT,
    actualizado_por INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (traslado_id) REFERENCES traslado(id) ON DELETE CASCADE,
    FOREIGN KEY (actualizado_por) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- ÍNDICES ADICIONALES
-- =============================================================

CREATE INDEX idx_documento_categoria ON documento(categoria_id);
CREATE INDEX idx_documento_activo ON documento(activo);
CREATE INDEX idx_encuesta_activa ON encuesta(activa);
CREATE INDEX idx_pregunta_encuesta ON pregunta(encuesta_id);
CREATE INDEX idx_traslado_estado ON traslado(estado);
CREATE INDEX idx_traslado_conductor ON traslado(conductor_id);
CREATE INDEX idx_traslado_fecha ON traslado(created_at);
CREATE INDEX idx_historial_traslado ON historial_estado(traslado_id);
CREATE INDEX idx_respuesta_encuesta ON respuesta_encuesta(encuesta_id);

-- Migration: GPS tracking + mapa interactivo de traslados
-- Execute: mysql -u root -p elyra < database/migration_ubicacion_traslados.sql

-- Agregar coordenadas a la tabla traslado
ALTER TABLE traslado
    ADD COLUMN origen_lat DECIMAL(10, 7) NULL AFTER origen,
    ADD COLUMN origen_lng DECIMAL(10, 7) NULL AFTER origen_lat,
    ADD COLUMN destino_lat DECIMAL(10, 7) NULL AFTER destino,
    ADD COLUMN destino_lng DECIMAL(10, 7) NULL AFTER destino_lat;

-- Tabla de ubicación actual del conductor (upsert por conductor_id)
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

-- Tabla de historial de ubicaciones (breadcrumb trail)
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

-- =============================================================
-- MIGRACION: Tabla de Noticias
-- Agrega la tabla `noticias` que falta en el esquema
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
    FOREIGN KEY (autor_id) REFERENCES usuario(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_noticias_activo ON noticias(activo);
CREATE INDEX IF NOT EXISTS idx_noticias_autor ON noticias(autor_id);
CREATE INDEX IF NOT EXISTS idx_noticias_created ON noticias(created_at);

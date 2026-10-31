-- Tabla de usuarios para el login (roles admin / cajero)
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario    VARCHAR(50)  NOT NULL UNIQUE,
    clave      VARCHAR(64)  NOT NULL,   -- hash SHA2-256
    rol        ENUM('admin','cajero') NOT NULL
) ENGINE = InnoDB;

-- Usuarios de prueba
INSERT INTO usuarios (usuario, clave, rol) VALUES
('admin',  SHA2('admin123', 256),  'admin'),
('cajero', SHA2('cajero123', 256), 'cajero');

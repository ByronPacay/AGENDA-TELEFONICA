-- Bloque de creacion de la base de datos principal del proyecto.
CREATE DATABASE IF NOT EXISTS bd_agenda_telefonica
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Bloque para seleccionar la base que se usara en las tablas.
USE bd_agenda_telefonica;

-- Bloque de tabla de usuarios para registro e inicio de sesion.
CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bloque de tabla de contactos con campos de pais, favorito y observaciones.
CREATE TABLE IF NOT EXISTS contactos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    pais_iso CHAR(2) NOT NULL DEFAULT 'GT',
    pais_nombre VARCHAR(80) NOT NULL DEFAULT 'Guatemala',
    codigo_pais VARCHAR(6) NOT NULL DEFAULT '+502',
    telefono VARCHAR(30) NOT NULL,
    telefono_e164 VARCHAR(20) DEFAULT NULL,
    correo VARCHAR(120) DEFAULT NULL,
    direccion VARCHAR(100) DEFAULT NULL,
    favorito TINYINT(1) NOT NULL DEFAULT 0,
    notas VARCHAR(255) DEFAULT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_contactos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT uk_contactos_usuario_telefono
        UNIQUE (usuario_id, telefono_e164)
) ENGINE=InnoDB;

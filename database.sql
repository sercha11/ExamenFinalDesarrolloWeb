CREATE DATABASE IF NOT EXISTS equipos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE equipos;

CREATE TABLE equipos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  tipo ENUM('Laptop','Proyector','Tablet','Cámara','Laboratorio') NOT NULL,
  descripcion VARCHAR(255) NULL,
  ubicacion VARCHAR(100) NOT NULL,
  estado ENUM('Disponible','Mantenimiento') NOT NULL DEFAULT 'Disponible',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE reservas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  equipo_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  carne VARCHAR(20) NOT NULL,
  fecha DATE NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  motivo VARCHAR(255) NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reserva_equipo
    FOREIGN KEY (equipo_id) REFERENCES equipos(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_horas CHECK (hora_fin > hora_inicio),
  INDEX idx_equipo_fecha (equipo_id, fecha)
) ENGINE=InnoDB;
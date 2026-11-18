CREATE DATABASE IF NOT EXISTS bdm_aseguradora CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE bdm_aseguradora;

CREATE TABLE IF NOT EXISTS rol (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS usuario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido_p VARCHAR(50) NOT NULL,
    apellido_m VARCHAR(50) NULL,
    nacimiento DATE NOT NULL,
    genero VARCHAR(20) NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    alias VARCHAR(50) NOT NULL,
    foto_perfil LONGBLOB NULL,
    estatus TINYINT UNSIGNED DEFAULT 1,
    rol_id TINYINT UNSIGNED NOT NULL,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES rol(id)
);

CREATE TABLE IF NOT EXISTS aseguradoras (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS vehiculo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(30) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    color VARCHAR(30) NULL,
    placas VARCHAR(10) NOT NULL UNIQUE,
    num_serie VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS poliza (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    num_poliza VARCHAR(50) NOT NULL,
    aseguradora_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    vehiculo_id INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_poliza_aseguradora (num_poliza, aseguradora_id),
    CONSTRAINT fk_poliza_aseguradora FOREIGN KEY (aseguradora_id) REFERENCES aseguradoras(id),
    CONSTRAINT fk_poliza_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id),
    CONSTRAINT fk_poliza_vehiculo FOREIGN KEY (vehiculo_id) REFERENCES vehiculo(id)
);

CREATE TABLE IF NOT EXISTS estatus (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS siniestro (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    direccion TEXT NOT NULL,
    descripcion TEXT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ajustador_id INT UNSIGNED NULL,
    poliza_id INT UNSIGNED NOT NULL,
    estatus_id TINYINT UNSIGNED NULL,
    CONSTRAINT fk_siniestro_poliza FOREIGN KEY (poliza_id) REFERENCES poliza(id),
    CONSTRAINT fk_siniestro_ajustador FOREIGN KEY (ajustador_id) REFERENCES usuario(id),
    CONSTRAINT fk_siniestro_estatus FOREIGN KEY (estatus_id) REFERENCES estatus(id)
);

CREATE TABLE IF NOT EXISTS historialestatus (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha_actu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    siniestro_id INT UNSIGNED NOT NULL,
    estatus_id TINYINT UNSIGNED NOT NULL,
    CONSTRAINT fk_historial_siniestro FOREIGN KEY (siniestro_id) REFERENCES siniestro(id),
    CONSTRAINT fk_historial_estatus FOREIGN KEY (estatus_id) REFERENCES estatus(id)
);

CREATE TABLE IF NOT EXISTS multimedia (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('Foto','Video') NOT NULL,
    archivo LONGBLOB NULL,
    ruta VARCHAR(255) NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    siniestro_id INT UNSIGNED NOT NULL,
    CONSTRAINT fk_multimedia_siniestro FOREIGN KEY (siniestro_id) REFERENCES siniestro(id)
);

CREATE TABLE IF NOT EXISTS evaluacion (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    diagnostico TEXT NOT NULL,
    fecha_evaluacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    siniestro_id INT UNSIGNED NOT NULL,
    ajustador_id INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_evaluacion_siniestro (siniestro_id),
    CONSTRAINT fk_evaluacion_siniestro FOREIGN KEY (siniestro_id) REFERENCES siniestro(id),
    CONSTRAINT fk_evaluacion_ajustador FOREIGN KEY (ajustador_id) REFERENCES usuario(id)
);

CREATE TABLE IF NOT EXISTS chat (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mensaje TEXT NOT NULL,
    fechamensaje TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    siniestro_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    mensaje_padre INT UNSIGNED NULL,
    CONSTRAINT fk_chat_siniestro FOREIGN KEY (siniestro_id) REFERENCES siniestro(id),
    CONSTRAINT fk_chat_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id),
    CONSTRAINT fk_chat_padre FOREIGN KEY (mensaje_padre) REFERENCES chat(id)
);

INSERT INTO rol (nombre, slug)
SELECT 'Asegurado', 'asegurado' WHERE NOT EXISTS (SELECT 1 FROM rol WHERE slug = 'asegurado');
INSERT INTO rol (nombre, slug)
SELECT 'Ajustador', 'ajustador' WHERE NOT EXISTS (SELECT 1 FROM rol WHERE slug = 'ajustador');
INSERT INTO rol (nombre, slug)
SELECT 'Supervisor', 'supervisor' WHERE NOT EXISTS (SELECT 1 FROM rol WHERE slug = 'supervisor');

INSERT INTO estatus (nombre)
SELECT 'En Evaluación' WHERE NOT EXISTS (SELECT 1 FROM estatus WHERE nombre = 'En Evaluación');
INSERT INTO estatus (nombre)
SELECT 'Aprobado' WHERE NOT EXISTS (SELECT 1 FROM estatus WHERE nombre = 'Aprobado');
INSERT INTO estatus (nombre)
SELECT 'Rechazado' WHERE NOT EXISTS (SELECT 1 FROM estatus WHERE nombre = 'Rechazado');
INSERT INTO estatus (nombre)
SELECT 'Pago en Proceso' WHERE NOT EXISTS (SELECT 1 FROM estatus WHERE nombre = 'Pago en Proceso');
INSERT INTO estatus (nombre)
SELECT 'Cerrado' WHERE NOT EXISTS (SELECT 1 FROM estatus WHERE nombre = 'Cerrado');

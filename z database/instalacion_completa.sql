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

USE bdm_aseguradora;

CREATE OR REPLACE VIEW vLogIn AS
SELECT u.id, u.alias, u.contrasena, u.rol_id, r.nombre AS NombreRol, r.slug
FROM usuario u
INNER JOIN rol r ON u.rol_id = r.id;

CREATE OR REPLACE VIEW vPolizas AS
SELECT
    p.id,
    p.usuario_id AS Usuario,
    u.nombre AS Cliente,
    p.num_poliza AS Poliza,
    v.marca AS Marca,
    v.modelo AS Modelo,
    a.nombre AS Aseguradora
FROM poliza p
INNER JOIN vehiculo v ON p.vehiculo_id = v.id
INNER JOIN aseguradoras a ON p.aseguradora_id = a.id
INNER JOIN usuario u ON p.usuario_id = u.id;

CREATE OR REPLACE VIEW vListarSiniestros AS
SELECT
    s.id,
    s.fecha,
    s.hora,
    s.direccion,
    s.descripcion,
    s.ajustador_id,
    vp.Poliza,
    vp.Usuario,
    vp.Cliente AS nombreCliente,
    vp.Marca,
    vp.Modelo,
    vp.Aseguradora,
    IFNULL(e.nombre, 'Sin Estatus') AS Estatus,
    IFNULL(u_ajustador.nombre, 'No Asignado') AS Ajustador
FROM siniestro s
INNER JOIN vPolizas vp ON s.poliza_id = vp.id
LEFT JOIN estatus e ON s.estatus_id = e.id
LEFT JOIN usuario u_ajustador ON s.ajustador_id = u_ajustador.id;

CREATE OR REPLACE VIEW vHistorialEstatus AS
SELECT hs.siniestro_id, e.nombre AS Estatus_Nuevo, hs.fecha_actu AS Fecha_Cambio
FROM historialestatus hs
INNER JOIN estatus e ON hs.estatus_id = e.id;

CREATE OR REPLACE VIEW vListarClientes AS
SELECT
    u.id AS Cliente_ID,
    CONCAT_WS(' ', u.nombre, u.apellido_p, NULLIF(u.apellido_m, '')) AS Nombre_Completo,
    u.email,
    COUNT(DISTINCT p.id) AS Total_Polizas,
    COUNT(DISTINCT s.id) AS Total_Siniestros
FROM usuario u
INNER JOIN rol r ON u.rol_id = r.id
LEFT JOIN poliza p ON u.id = p.usuario_id
LEFT JOIN siniestro s ON p.id = s.poliza_id
WHERE r.slug = 'asegurado'
GROUP BY u.id, u.nombre, u.apellido_p, u.apellido_m, u.email;

CREATE OR REPLACE VIEW vListarAjustadores AS
SELECT
    u.id AS Ajustador_ID,
    CONCAT_WS(' ', u.nombre, u.apellido_p) AS Nombre_Ajustador,
    COUNT(s.id) AS Siniestros_Asignados
FROM usuario u
INNER JOIN rol r ON u.rol_id = r.id
LEFT JOIN siniestro s ON u.id = s.ajustador_id
WHERE r.slug = 'ajustador'
GROUP BY u.id, u.nombre, u.apellido_p;

CREATE OR REPLACE VIEW vBusquedaAvanzada AS
SELECT
    s.id AS Folio_Siniestro,
    p.num_poliza AS Numero_Poliza,
    CONCAT_WS(' ', u.nombre, u.apellido_p) AS Cliente,
    v.marca AS Marca,
    v.modelo AS Modelo,
    v.placas AS Placas,
    e.nombre AS Estatus_Actual,
    s.fecha AS Fecha_Siniestro
FROM siniestro s
INNER JOIN poliza p ON s.poliza_id = p.id
INNER JOIN vehiculo v ON p.vehiculo_id = v.id
INNER JOIN usuario u ON p.usuario_id = u.id
LEFT JOIN estatus e ON s.estatus_id = e.id;

CREATE OR REPLACE VIEW vDashboardAdmin AS
SELECT e.nombre AS Estatus_Siniestro, COUNT(s.id) AS Cantidad_Total
FROM estatus e
LEFT JOIN siniestro s ON e.id = s.estatus_id
GROUP BY e.id, e.nombre;

USE bdm_aseguradora;

DROP PROCEDURE IF EXISTS sp_validar_usuario;
DROP PROCEDURE IF EXISTS sp_registrar_usuario;
DROP PROCEDURE IF EXISTS sp_listar_usuarios;
DROP PROCEDURE IF EXISTS sp_eliminar_usuario;
DROP PROCEDURE IF EXISTS sp_verPoliza;
DROP PROCEDURE IF EXISTS sp_InsertarPoliza;

DELIMITER $$

CREATE PROCEDURE sp_validar_usuario(IN p_alias VARCHAR(50), IN p_contrasena VARCHAR(255))
READS SQL DATA
SQL SECURITY INVOKER
BEGIN
    SELECT u.id, u.alias, u.rol_id, r.slug
    FROM usuario u
    INNER JOIN rol r ON r.id = u.rol_id
    WHERE u.alias = p_alias
      AND u.contrasena = p_contrasena
      AND u.estatus = 1
    LIMIT 1;
END$$

CREATE PROCEDURE sp_registrar_usuario(
    IN p_nombre VARCHAR(50),
    IN p_apellido_p VARCHAR(50),
    IN p_apellido_m VARCHAR(50),
    IN p_nacimiento DATE,
    IN p_genero VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_contrasena VARCHAR(255),
    IN p_alias VARCHAR(50),
    IN p_foto_perfil LONGBLOB
)
MODIFIES SQL DATA
SQL SECURITY INVOKER
BEGIN
    DECLARE v_rol_asegurado TINYINT UNSIGNED DEFAULT NULL;
    SELECT MIN(id) INTO v_rol_asegurado FROM rol WHERE slug = 'asegurado';
    IF v_rol_asegurado IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falta el rol asegurado.';
    END IF;
    INSERT INTO usuario (
        nombre, apellido_p, apellido_m, nacimiento, genero,
        email, contrasena, alias, foto_perfil, rol_id
    ) VALUES (
        p_nombre, p_apellido_p, p_apellido_m, p_nacimiento, p_genero,
        p_email, p_contrasena, p_alias, p_foto_perfil, v_rol_asegurado
    );
END$$

CREATE PROCEDURE sp_listar_usuarios()
READS SQL DATA
BEGIN
    SELECT id, nombre, apellido_p, email, alias, rol_id FROM usuario;
END$$

CREATE PROCEDURE sp_eliminar_usuario(IN p_id INT)
MODIFIES SQL DATA
BEGIN
    UPDATE usuario SET estatus = 0 WHERE id = p_id;
END$$

CREATE PROCEDURE sp_verPoliza(IN p_idUser INT)
READS SQL DATA
BEGIN
    SELECT id, Usuario, Cliente, Poliza, Marca, Modelo, Aseguradora
    FROM vPolizas
    WHERE Usuario = p_idUser;
END$$

CREATE PROCEDURE sp_InsertarPoliza(
    IN p_nPoliza VARCHAR(50),
    IN p_usuario_id INT,
    IN p_aseguradora_id INT,
    IN p_vehiculo INT
)
MODIFIES SQL DATA
BEGIN
    INSERT INTO poliza (num_poliza, aseguradora_id, usuario_id, vehiculo_id)
    VALUES (p_nPoliza, p_aseguradora_id, p_usuario_id, p_vehiculo);
END$$

DELIMITER ;

USE bdm_aseguradora;

-- El historial de estatus se registra desde SiniestroM::cambiarEstatus().
-- Este trigger únicamente asigna un ajustador al crear un siniestro cuando haya alguno disponible.
DROP TRIGGER IF EXISTS T_asignar_Ajust;
DROP TRIGGER IF EXISTS T_historial_estatus;

DELIMITER $$
CREATE TRIGGER T_asignar_Ajust
BEFORE INSERT ON siniestro
FOR EACH ROW
BEGIN
    DECLARE v_ajustador_id INT UNSIGNED DEFAULT NULL;
    SELECT u.id INTO v_ajustador_id
    FROM usuario u
    INNER JOIN rol r ON u.rol_id = r.id
    WHERE r.slug = 'ajustador' AND u.estatus = 1
    ORDER BY RAND()
    LIMIT 1;

    IF NEW.ajustador_id IS NULL AND v_ajustador_id IS NOT NULL THEN
        SET NEW.ajustador_id = v_ajustador_id;
    END IF;
END$$
DELIMITER ;

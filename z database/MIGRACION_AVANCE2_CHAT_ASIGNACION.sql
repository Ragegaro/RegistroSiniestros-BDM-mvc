-- Ejecutar UNA VEZ en phpMyAdmin > SQL, sobre la base EXISTENTE.
-- No elimina registros, no convierte videos a BLOB y mantiene pólizas/siniestros.
USE bdm_aseguradora;

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

-- En el avance 2 la asignación automática era un TRIGGER, no un SP.
-- El avance 3 lo retiró. Lo restauramos para asignar al crear cada caso.
-- No altera los responsables de siniestros existentes.
DROP TRIGGER IF EXISTS T_asignar_Ajust;

DELIMITER $$
CREATE TRIGGER T_asignar_Ajust
BEFORE INSERT ON siniestro
FOR EACH ROW
BEGIN
    DECLARE v_ajustador_id INT UNSIGNED DEFAULT NULL;

    IF NEW.ajustador_id IS NULL THEN
        SELECT u.id INTO v_ajustador_id
        FROM usuario u
        INNER JOIN rol r ON r.id = u.rol_id
        WHERE r.slug = 'ajustador' AND u.estatus = 1
        ORDER BY RAND()
        LIMIT 1;

        IF v_ajustador_id IS NOT NULL THEN
            SET NEW.ajustador_id = v_ajustador_id;
        END IF;
    END IF;
END$$

-- El SP verifica que la póliza sea del asegurado y que existan ajustadores.
-- El trigger anterior elige automáticamente uno al INSERTAR el siniestro.
DROP PROCEDURE IF EXISTS sp_registrarSiniestro$$
CREATE PROCEDURE sp_registrarSiniestro(
    IN p_fecha DATE,
    IN p_hora TIME,
    IN p_direccion TEXT,
    IN p_descripcion TEXT,
    IN p_poliza_id INT UNSIGNED,
    IN p_usuario_id INT UNSIGNED
)
MODIFIES SQL DATA
BEGIN
    DECLARE v_propietario INT UNSIGNED DEFAULT NULL;
    DECLARE v_ajustadores INT DEFAULT 0;

    SELECT MIN(usuario_id) INTO v_propietario
    FROM poliza WHERE id = p_poliza_id;

    IF v_propietario IS NULL OR v_propietario <> p_usuario_id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La poliza no pertenece al asegurado.';
    END IF;

    SELECT COUNT(*) INTO v_ajustadores
    FROM usuario u INNER JOIN rol r ON r.id = u.rol_id
    WHERE r.slug = 'ajustador' AND u.estatus = 1;

    IF v_ajustadores = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay ajustadores activos para asignar el reporte.';
    END IF;

    INSERT INTO siniestro (fecha, hora, direccion, descripcion, poliza_id)
    VALUES (p_fecha, p_hora, p_direccion, p_descripcion, p_poliza_id);

    SELECT LAST_INSERT_ID() AS siniestro_id;
END$$
DELIMITER ;

-- Comprobaciones de lectura (opcional):
-- SHOW TRIGGERS LIKE 'siniestro';
-- SHOW PROCEDURE STATUS WHERE Db = 'bdm_aseguradora' AND Name = 'sp_registrarSiniestro';
-- SELECT id, tipo, ruta, COALESCE(OCTET_LENGTH(archivo),0) AS bytes_blob FROM multimedia ORDER BY id DESC LIMIT 10;

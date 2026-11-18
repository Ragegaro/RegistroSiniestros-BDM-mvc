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

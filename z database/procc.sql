DELIMITER //
CREATE PROCEDURE sp_validar_usuario (IN p_alias VARCHAR(50),IN p_contrasena VARCHAR(255) )
BEGIN
    SELECT * FROM usuario 
    WHERE alias = p_alias 
      AND contrasena = p_contrasena; 
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_registrar_usuario (
    IN p_nombre VARCHAR(100), IN p_apellido_p VARCHAR(100), IN p_apellido_m VARCHAR(100),
    IN p_nacimiento DATE, IN p_genero VARCHAR(20), IN p_email VARCHAR(150),
    IN p_contrasena VARCHAR(255), IN p_alias VARCHAR(50), IN p_foto_perfil VARCHAR(255)
)
BEGIN
    INSERT INTO usuario (
        nombre, apellido_p, apellido_m, 
        nacimiento, genero, email, 
        contrasena, alias, foto_perfil, 
        rol_id
    ) 
    VALUES (
        p_nombre, p_apellido_p, p_apellido_m, 
        p_nacimiento, p_genero, p_email, 
        p_contrasena, p_alias, 
        p_foto_perfil, 
        3 -- El rol se asigna automáticamente aquí
    );
END //

DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_listar_usuarios()
BEGIN
    SELECT id, nombre, apellido_p, email, alias, rol_id 
    FROM usuario;
END //
DELIMITER ;



DELIMITER //

CREATE PROCEDURE sp_eliminar_usuario(
    IN p_id INT
)
BEGIN
    UPDATE usuario 
    SET estatus = 0 
    WHERE id = p_id;
END //

DELIMITER ;

/*
DELIMITER //
CREATE PROCEDURE sp_eliminar_usuario_def(
    IN p_id INT
)
BEGIN
    DELETE FROM usuario WHERE id_usuario = p_id;
END //
DELIMITER ;
*/







-- call sp_listar_usuarios()
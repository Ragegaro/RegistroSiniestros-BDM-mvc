/*DELIMITER//
CREATE PROCEDURE sp_gestionUsuario(
	IN _accion VARCHAR(20),
	
    IN _id INT,
    IN _alias varchar(50),
    IN _password varchar(255),
    
	IN _nombre varchar (50),
    IN _apellidoP varchar (100),
    IN _apellidoM varchar (100),
    IN _nacimiento DATE,
    IN _genero varchar(20),
    IN _email varchar (100),
    IN _fotoPerfil mediumblob 
)
BEGIN
IF _accion = 'LogIn' then

 
END//
DELIMITER ;
*/

-- VALIDAR USUARIO
DELIMITER //
CREATE PROCEDURE sp_validar_usuario (IN p_alias VARCHAR(50),IN p_contrasena VARCHAR(255) )
BEGIN
    SELECT * FROM usuario 
    WHERE alias = p_alias 
      AND contrasena = p_contrasena; 
END //
DELIMITER ;


-- REgistrar Usuario

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
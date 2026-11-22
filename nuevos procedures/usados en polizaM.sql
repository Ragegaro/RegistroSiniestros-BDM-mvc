DELIMITER //
CREATE PROCEDURE sp_listar_aseguradoras()
BEGIN
    SELECT id,nombre FROM aseguradoras ORDER BY	 nombre;
   END//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_obtener_vehiculos_usuario(IN p_usuario_id INT)
BEGIN
    SELECT DISTINCT
        v.id,
        v.marca,
        v.modelo,
        v.placas
    FROM vehiculo v
    INNER JOIN poliza p ON p.vehiculo_id = v.id
    WHERE p.usuario_id = p_usuario_id
    ORDER BY v.marca, v.modelo;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_Registrar_vehiculo(
IN p_marca VARCHAR(100),
IN p_modelo VARCHAR(100),
IN p_color VARCHAR(50),
IN p_placas VARCHAR(30),
IN p_num_serie VARCHAR(100),
OUT p_vehiculo_id INT
)
BEGIN
	INSERT INTO vehiculo(marca,modelo,color,placas,num_serie)
    values (p_marca, p_modelo, p_color, p_placas, p_num_serie);
		SET p_vehiculo_id = last_insert_id();
END//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_obtener_o_crear_aseguradora (
    IN p_nombre VARCHAR(150)
)
BEGIN
    DECLARE v_id INT DEFAULT NULL;

    SELECT id INTO v_id
    FROM aseguradoras
    WHERE nombre = p_nombre
    ORDER BY id
    LIMIT 1;

    IF v_id IS NULL THEN
        INSERT INTO aseguradoras (nombre)
        VALUES (p_nombre);

        SET v_id = LAST_INSERT_ID();
    END IF;

    SELECT v_id AS aseguradora_id;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_registrar_poliza_V(
    IN p_num_poliza VARCHAR(50),
    IN p_aseguradora_id INT,
    IN p_usuario_id INT,
    IN p_vehiculo_id INT
)
BEGIN
    INSERT INTO poliza (
        num_poliza,
        aseguradora_id,
        usuario_id,
        vehiculo_id
    ) VALUES (
        p_num_poliza,
        p_aseguradora_id,
        p_usuario_id,
        p_vehiculo_id
    );
END//
DELIMITER ;


DELIMITER //
CREATE PROCEDURE sp_validar_aseguradora_existe(
    IN p_aseguradora_id INT,
    OUT p_existe TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_existe
    FROM aseguradoras
    WHERE id = p_aseguradora_id;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_validar_vehiculo_usuario(
    IN p_usuario_id INT,
    IN p_vehiculo_id INT,
    OUT p_pertenece TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_pertenece
    FROM poliza
    WHERE usuario_id = p_usuario_id
      AND vehiculo_id = p_vehiculo_id;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_validar_poliza_duplicada(
    IN p_num_poliza VARCHAR(50),
    IN p_aseguradora_id INT,
    OUT p_existe TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_existe
    FROM poliza
    WHERE num_poliza = p_num_poliza
      AND aseguradora_id = p_aseguradora_id;
END //
DELIMITER ;


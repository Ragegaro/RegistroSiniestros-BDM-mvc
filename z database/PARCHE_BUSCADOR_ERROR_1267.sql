-- PARCHE INCREMENTAL DE COLLATION PARA EL BUSCADOR
-- Destino: bdm_aseguradora / MariaDB (XAMPP)
-- No modifica ni elimina datos. Recrea solamente sp_buscar_siniestros.
-- Realiza un respaldo de la BD antes de instalar.
USE bdm_aseguradora;

DROP PROCEDURE IF EXISTS sp_buscar_siniestros;
DELIMITER $$
CREATE PROCEDURE sp_buscar_siniestros(
    IN p_actor INT UNSIGNED,
    IN p_texto VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    IN p_estatus INT,
    IN p_ajustador INT,
    IN p_desde DATE,
    IN p_hasta DATE
)
READS SQL DATA
BEGIN
    DECLARE v_rol VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL;
    DECLARE v_busqueda VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '';

    SELECT r.slug INTO v_rol
      FROM usuario u
      JOIN rol r ON r.id = u.rol_id
     WHERE u.id = p_actor AND u.estatus = 1
     LIMIT 1;

    IF v_rol IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Usuario inactivo o no encontrado.';
    END IF;

    SET v_busqueda = COALESCE(TRIM(CONVERT(p_texto USING utf8mb4)), '');

    SELECT v.*
      FROM vListarSiniestros v
      JOIN siniestro s ON s.id = v.id
     WHERE (
        v_rol IN ('supervisor', 'admin')
        OR (v_rol = 'ajustador' AND v.ajustador_id = p_actor)
        OR (v_rol = 'asegurado' AND v.Usuario = p_actor)
     )
       AND (
        v_busqueda = ''
        OR LOCATE(v_busqueda, CONVERT(CAST(v.id AS CHAR) USING utf8mb4) COLLATE utf8mb4_general_ci) > 0
        OR LOCATE(v_busqueda, CONVERT(v.Poliza USING utf8mb4) COLLATE utf8mb4_general_ci) > 0
        OR LOCATE(v_busqueda, CONVERT(v.nombreCliente USING utf8mb4) COLLATE utf8mb4_general_ci) > 0
        OR LOCATE(v_busqueda, CONVERT(v.Ajustador USING utf8mb4) COLLATE utf8mb4_general_ci) > 0
        OR LOCATE(v_busqueda, CONVERT(v.Marca USING utf8mb4) COLLATE utf8mb4_general_ci) > 0
        OR LOCATE(v_busqueda, CONVERT(v.Modelo USING utf8mb4) COLLATE utf8mb4_general_ci) > 0
        OR LOCATE(v_busqueda, CONVERT(s.direccion USING utf8mb4) COLLATE utf8mb4_general_ci) > 0
        OR EXISTS (
            SELECT 1 FROM poliza p
            JOIN vehiculo au ON au.id = p.vehiculo_id
            WHERE p.id = s.poliza_id
              AND LOCATE(v_busqueda, CONVERT(au.placas USING utf8mb4) COLLATE utf8mb4_general_ci) > 0
        )
     )
       AND (p_estatus IS NULL OR p_estatus = 0 OR (p_estatus = -1 AND s.estatus_id IS NULL) OR s.estatus_id = p_estatus)
       AND (p_ajustador IS NULL OR p_ajustador = 0 OR (p_ajustador = -1 AND s.ajustador_id IS NULL) OR s.ajustador_id = p_ajustador)
       AND (p_desde IS NULL OR s.fecha >= p_desde)
       AND (p_hasta IS NULL OR s.fecha <= p_hasta)
     ORDER BY s.fecha DESC, s.id DESC
     LIMIT 500;
END$$
DELIMITER ;

-- PRUEBA: usa el ID real de un usuario asegurado activo, en lugar de 2.
-- CALL sp_buscar_siniestros(2, '', 0, 0, NULL, NULL);

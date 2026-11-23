-- DEFINICION DIDACTICA: DOS TRIGGERS (ya incluidos en la migracion principal).
-- Si ejecutas la migracion, NO es necesario ejecutar este archivo aparte.
-- Se debe crear primero sp_seleccionar_ajustador (incluido en la migracion).
USE bdm_aseguradora;
DROP TRIGGER IF EXISTS T_asignar_Ajust;
DROP PROCEDURE IF EXISTS sp_seleccionar_ajustador;
DELIMITER $$
CREATE PROCEDURE sp_seleccionar_ajustador(OUT p_id INT UNSIGNED)
READS SQL DATA
BEGIN
  SET p_id = NULL;
  SELECT u.id INTO p_id FROM usuario u JOIN rol r ON r.id=u.rol_id
    WHERE r.slug='ajustador' AND u.estatus=1
    ORDER BY RAND() LIMIT 1;
END$$
CREATE TRIGGER T_asignar_Ajust BEFORE INSERT ON siniestro FOR EACH ROW
BEGIN
  DECLARE v_id INT UNSIGNED DEFAULT NULL;
  IF NEW.ajustador_id IS NULL THEN
    CALL sp_seleccionar_ajustador(v_id);
    IF v_id IS NULL THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No hay ajustadores activos disponibles.';
    END IF;
    SET NEW.ajustador_id = v_id;
  END IF;
END$$
DELIMITER ;

-- Trigger #2: bitacora automatica, sin INSERT manual desde PHP.
DROP TRIGGER IF EXISTS T_historial_estatus;
DELIMITER $$
CREATE TRIGGER T_historial_estatus AFTER UPDATE ON siniestro FOR EACH ROW
BEGIN
  IF NOT (NEW.estatus_id <=> OLD.estatus_id) AND NEW.estatus_id IS NOT NULL THEN
    INSERT INTO historialestatus(siniestro_id, estatus_id) VALUES (NEW.id, NEW.estatus_id);
  END IF;
END$$
DELIMITER ;


DELIMITER //
CREATE TRIGGER T_asignar_Ajust
BEFORE INSERT ON siniestro
FOR EACH ROW
BEGIN
    DECLARE v_ajustador_id INT;
    
    SELECT u.id INTO v_ajustador_id 
    FROM usuario u 
    JOIN rol r ON u.rol_id = r.id 
    WHERE r.slug = 'ajustador' 
    ORDER BY RAND() LIMIT 1;
    
    IF v_ajustador_id IS NOT NULL THEN
        SET NEW.ajustador_id = v_ajustador_id;
    END IF;
END; //
DELIMITER ;

DELIMITER //
CREATE TRIGGER T_historial_estatus
AFTER UPDATE ON siniestro
FOR EACH ROW
BEGIN
    -- NOT (...) <=> (...) evalúa correctamente los cambios aunque haya NULLs
    IF NOT (OLD.estatus_id <=> NEW.estatus_id) THEN
        INSERT INTO historialestatus (siniestro_id, estatus_id, fecha_actu) 
        VALUES (NEW.id, NEW.estatus_id, NOW());
    END IF;
END; //
DELIMITER ;



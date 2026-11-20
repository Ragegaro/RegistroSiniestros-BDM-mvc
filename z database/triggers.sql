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

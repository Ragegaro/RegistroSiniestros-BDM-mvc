-- DEFINICION DIDACTICA: DOS FUNCIONES (ya incluidas en la migracion principal).
-- No se necesita ejecutar este archivo si ya corriste ACTUALIZACION_SQL_TOTAL_BUSCADOR.sql.
USE bdm_aseguradora;
DROP FUNCTION IF EXISTS fn_puede_ver_siniestro;
DROP FUNCTION IF EXISTS fn_ajustador_activo;
DELIMITER $$
CREATE FUNCTION fn_ajustador_activo(p_usuario INT UNSIGNED)
RETURNS TINYINT READS SQL DATA
BEGIN
  DECLARE v_activo TINYINT DEFAULT 0;
  SELECT COUNT(*) INTO v_activo FROM usuario u JOIN rol r ON r.id=u.rol_id
    WHERE u.id=p_usuario AND u.estatus=1 AND r.slug='ajustador';
  RETURN IF(v_activo>0,1,0);
END$$

CREATE FUNCTION fn_puede_ver_siniestro(p_usuario INT UNSIGNED, p_siniestro INT UNSIGNED)
RETURNS TINYINT READS SQL DATA
BEGIN
  DECLARE v_permitido INT DEFAULT 0;
  SELECT COUNT(*) INTO v_permitido FROM siniestro s
    JOIN poliza p ON p.id=s.poliza_id
    JOIN usuario u ON u.id=p_usuario AND u.estatus=1
    JOIN rol r ON r.id=u.rol_id
    WHERE s.id=p_siniestro AND (
       r.slug IN ('supervisor','admin') OR
       (r.slug='ajustador' AND s.ajustador_id=p_usuario) OR
       (r.slug='asegurado' AND p.usuario_id=p_usuario)
    );
  RETURN IF(v_permitido>0,1,0);
END$$
DELIMITER ;


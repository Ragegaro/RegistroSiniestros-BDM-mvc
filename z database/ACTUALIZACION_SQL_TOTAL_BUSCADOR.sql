-- MIGRACION INCREMENTAL, SIN DROP TABLE / SIN BORRAR DATOS.
-- Aplicar DESPUES de importar la base original en phpMyAdmin, pestaña SQL.
-- El PHP modificado solo invoca procedimientos. Probado estaticamente; validar en MariaDB local.
USE bdm_aseguradora;

-- Las vistas NO vienen completas en el volcado del GitHub; hay que crearlas aqui.
CREATE OR REPLACE VIEW vLogIn AS
SELECT u.id, u.alias, u.contrasena, u.rol_id, r.nombre AS NombreRol, r.slug
FROM usuario u
INNER JOIN rol r ON u.rol_id = r.id;

CREATE OR REPLACE VIEW vPolizas AS
SELECT
    p.id,
    p.usuario_id AS Usuario,
    u.nombre AS Cliente,
    p.num_poliza AS Poliza,
    v.marca AS Marca,
    v.modelo AS Modelo,
    a.nombre AS Aseguradora
FROM poliza p
INNER JOIN vehiculo v ON p.vehiculo_id = v.id
INNER JOIN aseguradoras a ON p.aseguradora_id = a.id
INNER JOIN usuario u ON p.usuario_id = u.id;

CREATE OR REPLACE VIEW vListarSiniestros AS
SELECT
    s.id,
    s.fecha,
    s.hora,
    s.direccion,
    s.descripcion,
    s.ajustador_id,
    vp.Poliza,
    vp.Usuario,
    vp.Cliente AS nombreCliente,
    vp.Marca,
    vp.Modelo,
    vp.Aseguradora,
    IFNULL(e.nombre, 'Sin Estatus') AS Estatus,
    IFNULL(u_ajustador.nombre, 'No Asignado') AS Ajustador
FROM siniestro s
INNER JOIN vPolizas vp ON s.poliza_id = vp.id
LEFT JOIN estatus e ON s.estatus_id = e.id
LEFT JOIN usuario u_ajustador ON s.ajustador_id = u_ajustador.id;

CREATE OR REPLACE VIEW vHistorialEstatus AS
SELECT hs.siniestro_id, e.nombre AS Estatus_Nuevo, hs.fecha_actu AS Fecha_Cambio
FROM historialestatus hs
INNER JOIN estatus e ON hs.estatus_id = e.id;

CREATE OR REPLACE VIEW vListarClientes AS
SELECT
    u.id AS Cliente_ID,
    CONCAT_WS(' ', u.nombre, u.apellido_p, NULLIF(u.apellido_m, '')) AS Nombre_Completo,
    u.email,
    COUNT(DISTINCT p.id) AS Total_Polizas,
    COUNT(DISTINCT s.id) AS Total_Siniestros
FROM usuario u
INNER JOIN rol r ON u.rol_id = r.id
LEFT JOIN poliza p ON u.id = p.usuario_id
LEFT JOIN siniestro s ON p.id = s.poliza_id
WHERE r.slug = 'asegurado'
GROUP BY u.id, u.nombre, u.apellido_p, u.apellido_m, u.email;

CREATE OR REPLACE VIEW vListarAjustadores AS
SELECT
    u.id AS Ajustador_ID,
    CONCAT_WS(' ', u.nombre, u.apellido_p) AS Nombre_Ajustador,
    COUNT(s.id) AS Siniestros_Asignados
FROM usuario u
INNER JOIN rol r ON u.rol_id = r.id
LEFT JOIN siniestro s ON u.id = s.ajustador_id
WHERE r.slug = 'ajustador'
GROUP BY u.id, u.nombre, u.apellido_p;

CREATE OR REPLACE VIEW vBusquedaAvanzada AS
SELECT
    s.id AS Folio_Siniestro,
    p.num_poliza AS Numero_Poliza,
    CONCAT_WS(' ', u.nombre, u.apellido_p) AS Cliente,
    v.marca AS Marca,
    v.modelo AS Modelo,
    v.placas AS Placas,
    e.nombre AS Estatus_Actual,
    s.fecha AS Fecha_Siniestro
FROM siniestro s
INNER JOIN poliza p ON s.poliza_id = p.id
INNER JOIN vehiculo v ON p.vehiculo_id = v.id
INNER JOIN usuario u ON p.usuario_id = u.id
LEFT JOIN estatus e ON s.estatus_id = e.id;

CREATE OR REPLACE VIEW vDashboardAdmin AS
SELECT e.nombre AS Estatus_Siniestro, COUNT(s.id) AS Cantidad_Total
FROM estatus e
LEFT JOIN siniestro s ON e.id = s.estatus_id
GROUP BY e.id, e.nombre;

-- Indices opcionales omitidos para mantener compatible el esquema ya instalado.

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

-- Trigger #1: insercion automatica. Se invoca un SP con parametro OUT,
-- sin devolver resultados al cliente; MariaDB lo admite desde triggers.
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

DROP PROCEDURE IF EXISTS sp_registrarSiniestro;
DROP PROCEDURE IF EXISTS sp_catalogo_ajustadores_busqueda;
DROP PROCEDURE IF EXISTS sp_listar_aseguradoras;
DROP PROCEDURE IF EXISTS sp_obtener_vehiculos_usuario;
DROP PROCEDURE IF EXISTS sp_buscar_siniestros;
DROP PROCEDURE IF EXISTS sp_detalle_siniestro;
DROP PROCEDURE IF EXISTS sp_listar_multimedia;
DROP PROCEDURE IF EXISTS sp_ajustadores_activos;
DROP PROCEDURE IF EXISTS sp_reasignar_ajustador;
DROP PROCEDURE IF EXISTS sp_historial_siniestro;
DROP PROCEDURE IF EXISTS sp_catalogo_estatus;
DROP PROCEDURE IF EXISTS sp_cambiar_estatus;
DROP PROCEDURE IF EXISTS sp_guardar_evaluacion;
DROP PROCEDURE IF EXISTS sp_obtener_evaluacion;
DROP PROCEDURE IF EXISTS sp_dashboard_estados;
DROP PROCEDURE IF EXISTS sp_dashboard_ajustadores;
DROP PROCEDURE IF EXISTS sp_dashboard_clientes;
DROP PROCEDURE IF EXISTS sp_usuario_obtener;
DROP PROCEDURE IF EXISTS sp_usuario_editar;
DROP PROCEDURE IF EXISTS sp_vehiculo_registrar;
DROP PROCEDURE IF EXISTS sp_poliza_registrar_completa;
DROP PROCEDURE IF EXISTS sp_multimedia_guardar_ruta;
DROP PROCEDURE IF EXISTS sp_multimedia_metadatos;
DROP PROCEDURE IF EXISTS sp_multimedia_bloque;
DROP PROCEDURE IF EXISTS sp_chat_contexto;
DROP PROCEDURE IF EXISTS sp_chat_leer;
DROP PROCEDURE IF EXISTS sp_chat_enviar;
DELIMITER $$

CREATE PROCEDURE sp_listar_aseguradoras()
READS SQL DATA
BEGIN SELECT id,nombre FROM aseguradoras ORDER BY nombre; END$$
CREATE PROCEDURE sp_obtener_vehiculos_usuario(IN p_usuario INT UNSIGNED)
READS SQL DATA
BEGIN
 SELECT DISTINCT v.id,v.marca,v.modelo,v.placas FROM vehiculo v
 JOIN poliza p ON p.vehiculo_id=v.id WHERE p.usuario_id=p_usuario
 ORDER BY v.marca,v.modelo;
END$$

CREATE PROCEDURE sp_catalogo_ajustadores_busqueda(IN p_supervisor INT UNSIGNED)
READS SQL DATA
BEGIN
 IF NOT EXISTS(SELECT 1 FROM usuario u JOIN rol r ON r.id=u.rol_id WHERE u.id=p_supervisor AND u.estatus=1 AND r.slug IN ('supervisor','admin')) THEN
   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Solo supervisor puede consultar este catalogo.';
 END IF;
 SELECT u.id,CONCAT_WS(' ',u.nombre,u.apellido_p,NULLIF(u.apellido_m,'')) AS nombre,
   u.estatus AS activo FROM usuario u JOIN rol r ON r.id=u.rol_id
 WHERE r.slug='ajustador' ORDER BY nombre,u.id;
END$$

-- Reporte protegido: comprueba que la poliza pertenece al actor.
-- El trigger asigna automaticamente el ajustador al INSERT.
CREATE PROCEDURE sp_registrarSiniestro(
 IN p_fecha DATE, IN p_hora TIME, IN p_direccion TEXT,
 IN p_descripcion TEXT, IN p_poliza_id INT UNSIGNED, IN p_usuario_id INT UNSIGNED)
MODIFIES SQL DATA
BEGIN
 DECLARE v_propietario INT UNSIGNED DEFAULT NULL;
 SELECT MIN(p.usuario_id) INTO v_propietario FROM poliza p
 JOIN usuario u ON u.id=p.usuario_id AND u.estatus=1
 JOIN rol r ON r.id=u.rol_id AND r.slug='asegurado'
 WHERE p.id=p_poliza_id;
 IF v_propietario IS NULL OR v_propietario<>p_usuario_id THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La poliza no pertenece al asegurado.';
 END IF;
 INSERT INTO siniestro(fecha,hora,direccion,descripcion,poliza_id)
 VALUES(p_fecha,p_hora,p_direccion,p_descripcion,p_poliza_id);
 SELECT LAST_INSERT_ID() AS siniestro_id;
END$$

-- FILTROS con permisos en DB: no se acepta rol enviado por navegador ni por PHP.
CREATE PROCEDURE sp_buscar_siniestros(
 IN p_actor INT UNSIGNED, IN p_texto VARCHAR(150), IN p_estatus INT,
 IN p_ajustador INT, IN p_desde DATE, IN p_hasta DATE
)
READS SQL DATA
BEGIN
  DECLARE v_rol VARCHAR(50) DEFAULT NULL;
  SELECT r.slug INTO v_rol FROM usuario u JOIN rol r ON r.id=u.rol_id
    WHERE u.id=p_actor AND u.estatus=1 LIMIT 1;
  IF v_rol IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Usuario inactivo o no encontrado.'; END IF;
  SELECT v.* FROM vListarSiniestros v
    JOIN siniestro s ON s.id=v.id
    WHERE (v_rol IN ('supervisor','admin')
        OR (v_rol='ajustador' AND v.ajustador_id=p_actor)
        OR (v_rol='asegurado' AND v.Usuario=p_actor))
      AND (p_texto IS NULL OR TRIM(p_texto)='' OR (
        CAST(v.id AS CHAR) LIKE CONCAT('%',TRIM(p_texto),'%') OR
        v.Poliza LIKE CONCAT('%',TRIM(p_texto),'%') OR
        v.nombreCliente LIKE CONCAT('%',TRIM(p_texto),'%') OR
        v.Ajustador LIKE CONCAT('%',TRIM(p_texto),'%') OR
        v.Marca LIKE CONCAT('%',TRIM(p_texto),'%') OR
        v.Modelo LIKE CONCAT('%',TRIM(p_texto),'%') OR
        s.direccion LIKE CONCAT('%',TRIM(p_texto),'%') OR
        EXISTS (SELECT 1 FROM poliza p JOIN vehiculo au ON au.id=p.vehiculo_id
                WHERE p.id=s.poliza_id AND au.placas LIKE CONCAT('%',TRIM(p_texto),'%'))
      ))
      AND (p_estatus IS NULL OR p_estatus=0 OR (p_estatus=-1 AND s.estatus_id IS NULL) OR s.estatus_id=p_estatus)
      AND (p_ajustador IS NULL OR p_ajustador=0 OR (p_ajustador=-1 AND s.ajustador_id IS NULL) OR s.ajustador_id=p_ajustador)
      AND (p_desde IS NULL OR s.fecha>=p_desde)
      AND (p_hasta IS NULL OR s.fecha<=p_hasta)
    ORDER BY s.fecha DESC, s.id DESC LIMIT 500;
END$$

CREATE PROCEDURE sp_detalle_siniestro(IN p_id INT UNSIGNED, IN p_actor INT UNSIGNED)
READS SQL DATA
BEGIN
 SELECT * FROM vListarSiniestros WHERE id=p_id AND fn_puede_ver_siniestro(p_actor,p_id)=1;
END$$
CREATE PROCEDURE sp_listar_multimedia(IN p_id INT UNSIGNED, IN p_actor INT UNSIGNED)
READS SQL DATA
BEGIN
 IF fn_puede_ver_siniestro(p_actor,p_id)=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Acceso denegado a evidencias.'; END IF;
 SELECT id,tipo,ruta,COALESCE(OCTET_LENGTH(archivo),0) AS bytes FROM multimedia WHERE siniestro_id=p_id ORDER BY id;
END$$
CREATE PROCEDURE sp_ajustadores_activos(IN p_actor INT UNSIGNED)
READS SQL DATA
BEGIN
 IF NOT EXISTS(SELECT 1 FROM usuario u JOIN rol r ON r.id=u.rol_id WHERE u.id=p_actor AND u.estatus=1 AND r.slug IN ('supervisor','admin')) THEN
   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Solo supervisor puede ver ajustes de asignacion.';
 END IF;
 SELECT u.id,CONCAT_WS(' ',u.nombre,u.apellido_p,NULLIF(u.apellido_m,'')) AS nombre,
   u.alias,COUNT(s.id) AS casos FROM usuario u JOIN rol r ON r.id=u.rol_id
 LEFT JOIN siniestro s ON s.ajustador_id=u.id
 WHERE r.slug='ajustador' AND u.estatus=1
 GROUP BY u.id,u.nombre,u.apellido_p,u.apellido_m,u.alias ORDER BY nombre,u.id;
END$$
CREATE PROCEDURE sp_reasignar_ajustador(IN p_id INT UNSIGNED, IN p_ajustador INT UNSIGNED, IN p_actor INT UNSIGNED)
MODIFIES SQL DATA
BEGIN
 DECLARE v_estado VARCHAR(100) DEFAULT NULL;
 DECLARE v_existe INT DEFAULT 0;
 IF NOT EXISTS(SELECT 1 FROM usuario u JOIN rol r ON r.id=u.rol_id WHERE u.id=p_actor AND u.estatus=1 AND r.slug IN ('supervisor','admin')) THEN
   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Solo supervisor puede reasignar expedientes.';
 END IF;
 SELECT COUNT(*) INTO v_existe FROM siniestro WHERE id=p_id;
 IF v_existe=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Siniestro no encontrado.'; END IF;
 SELECT e.nombre INTO v_estado FROM siniestro s LEFT JOIN estatus e ON e.id=s.estatus_id
   WHERE s.id=p_id FOR UPDATE;
 IF LOWER(COALESCE(v_estado,''))='cerrado' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No se puede reasignar un expediente cerrado.'; END IF;
 IF fn_ajustador_activo(p_ajustador)=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Selecciona un ajustador activo.'; END IF;
 UPDATE siniestro SET ajustador_id=p_ajustador WHERE id=p_id;
END$$
CREATE PROCEDURE sp_historial_siniestro(IN p_id INT UNSIGNED, IN p_actor INT UNSIGNED)
READS SQL DATA
BEGIN
 IF fn_puede_ver_siniestro(p_actor,p_id)=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No autorizado.'; END IF;
 SELECT h.id,h.fecha_actu,h.estatus_id,e.nombre AS estatus FROM historialestatus h
 JOIN estatus e ON e.id=h.estatus_id WHERE h.siniestro_id=p_id ORDER BY h.fecha_actu DESC,h.id DESC;
END$$
CREATE PROCEDURE sp_catalogo_estatus()
READS SQL DATA
BEGIN SELECT id,nombre FROM estatus ORDER BY id; END$$
CREATE PROCEDURE sp_cambiar_estatus(IN p_id INT UNSIGNED, IN p_estado INT UNSIGNED, IN p_actor INT UNSIGNED)
MODIFIES SQL DATA
BEGIN
 DECLARE v_estado_anterior INT DEFAULT NULL;
 DECLARE v_nombre VARCHAR(100) DEFAULT NULL;
 DECLARE v_responsable INT DEFAULT NULL;
 DECLARE v_rol VARCHAR(50) DEFAULT NULL;
 DECLARE v_existe INT DEFAULT 0;
 SELECT r.slug INTO v_rol FROM usuario u JOIN rol r ON r.id=u.rol_id WHERE u.id=p_actor AND u.estatus=1 LIMIT 1;
 SELECT COUNT(*) INTO v_existe FROM siniestro WHERE id=p_id;
 IF v_existe=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Siniestro no encontrado.'; END IF;
 SELECT s.estatus_id,s.ajustador_id,e.nombre INTO v_estado_anterior,v_responsable,v_nombre
 FROM siniestro s LEFT JOIN estatus e ON e.id=s.estatus_id WHERE s.id=p_id FOR UPDATE;
 IF NOT (v_rol IN ('supervisor','admin') OR (v_rol='ajustador' AND v_responsable=p_actor)) THEN
   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Solo supervisor o ajustador asignado puede cambiar estatus.';
 END IF;
 IF LOWER(COALESCE(v_nombre,''))='cerrado' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El expediente ya esta cerrado.'; END IF;
 IF NOT EXISTS(SELECT 1 FROM estatus WHERE id=p_estado) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Estatus invalido.'; END IF;
 IF NOT (v_estado_anterior <=> p_estado) THEN
  UPDATE siniestro SET estatus_id=p_estado WHERE id=p_id;
  -- T_historial_estatus registra automaticamente el movimiento.
 END IF;
END$$
CREATE PROCEDURE sp_guardar_evaluacion(IN p_id INT UNSIGNED, IN p_actor INT UNSIGNED, IN p_diagnostico TEXT)
MODIFIES SQL DATA
BEGIN
 DECLARE v_rol VARCHAR(50) DEFAULT NULL;
 DECLARE v_responsable INT DEFAULT NULL;
 DECLARE v_existe INT DEFAULT 0;
 SELECT r.slug INTO v_rol FROM usuario u JOIN rol r ON r.id=u.rol_id WHERE u.id=p_actor AND u.estatus=1 LIMIT 1;
 SELECT COUNT(*),MAX(ajustador_id) INTO v_existe,v_responsable FROM siniestro WHERE id=p_id;
 IF v_existe=0 OR NOT (v_rol IN ('supervisor','admin') OR (v_rol='ajustador' AND v_responsable=p_actor)) THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No tienes acceso para evaluar este expediente.';
 END IF;
 IF TRIM(COALESCE(p_diagnostico,''))='' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Escribe el diagnostico.'; END IF;
 INSERT INTO evaluacion(diagnostico,siniestro_id,ajustador_id) VALUES (p_diagnostico,p_id,p_actor)
 ON DUPLICATE KEY UPDATE diagnostico=VALUES(diagnostico),ajustador_id=VALUES(ajustador_id),fecha_evaluacion=CURRENT_TIMESTAMP;
END$$
CREATE PROCEDURE sp_obtener_evaluacion(IN p_id INT UNSIGNED, IN p_actor INT UNSIGNED)
READS SQL DATA
BEGIN
 IF fn_puede_ver_siniestro(p_actor,p_id)=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No autorizado.'; END IF;
 SELECT e.*,u.nombre AS nombre_ajustador,u.apellido_p AS apellido_ajustador
 FROM evaluacion e JOIN usuario u ON u.id=e.ajustador_id WHERE e.siniestro_id=p_id LIMIT 1;
END$$
CREATE PROCEDURE sp_dashboard_estados()
READS SQL DATA BEGIN SELECT * FROM vDashboardAdmin; END$$
CREATE PROCEDURE sp_dashboard_ajustadores()
READS SQL DATA BEGIN SELECT * FROM vListarAjustadores; END$$
CREATE PROCEDURE sp_dashboard_clientes()
READS SQL DATA BEGIN SELECT * FROM vListarClientes LIMIT 10; END$$
CREATE PROCEDURE sp_usuario_obtener(IN p_id INT UNSIGNED)
READS SQL DATA BEGIN
 SELECT nombre,apellido_p,apellido_m,nacimiento,genero,email,alias,foto_perfil,rol_id FROM usuario WHERE id=p_id;
END$$
CREATE PROCEDURE sp_usuario_editar(IN p_id INT UNSIGNED, IN p_email VARCHAR(100), IN p_alias VARCHAR(50),
 IN p_password VARCHAR(255), IN p_foto LONGBLOB, IN p_cambiar_password TINYINT, IN p_cambiar_foto TINYINT)
MODIFIES SQL DATA
BEGIN
 UPDATE usuario SET email=p_email,alias=p_alias,
 contrasena=CASE WHEN p_cambiar_password=1 THEN p_password ELSE contrasena END,
 foto_perfil=CASE WHEN p_cambiar_foto=1 THEN p_foto ELSE foto_perfil END
 WHERE id=p_id;
END$$
CREATE PROCEDURE sp_vehiculo_registrar(IN p_marca VARCHAR(30), IN p_modelo VARCHAR(50),
 IN p_color VARCHAR(30), IN p_placas VARCHAR(10), IN p_serie VARCHAR(50))
MODIFIES SQL DATA
BEGIN INSERT INTO vehiculo(marca,modelo,color,placas,num_serie)
 VALUES(p_marca,p_modelo,p_color,p_placas,p_serie); END$$

-- Todo el alta de poliza en 1 SP. PHP NO usa SELECT @variables de salida.
-- Transaccion abierta desde PHP: rollback si falla cualquier paso.
CREATE PROCEDURE sp_poliza_registrar_completa(
 IN p_actor INT UNSIGNED, IN p_numero VARCHAR(50), IN p_aseguradora INT,
 IN p_aseguradora_nueva VARCHAR(100), IN p_modo VARCHAR(20), IN p_vehiculo INT,
 IN p_marca VARCHAR(30), IN p_modelo VARCHAR(50), IN p_color VARCHAR(30),
 IN p_placas VARCHAR(10), IN p_serie VARCHAR(50))
MODIFIES SQL DATA
BEGIN
 DECLARE v_aseguradora INT UNSIGNED DEFAULT NULL;
 DECLARE v_vehiculo INT UNSIGNED DEFAULT NULL;
 DECLARE v_existe INT DEFAULT 0;
 IF NOT EXISTS(SELECT 1 FROM usuario u JOIN rol r ON r.id=u.rol_id
    WHERE u.id=p_actor AND u.estatus=1 AND r.slug='asegurado') THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Solo asegurados activos pueden registrar polizas.';
 END IF;
 IF TRIM(COALESCE(p_numero,''))='' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Falta el numero de poliza.'; END IF;
 IF p_aseguradora IS NULL OR p_aseguradora=0 THEN
  IF TRIM(COALESCE(p_aseguradora_nueva,''))='' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Escribe una aseguradora.'; END IF;
  SELECT MIN(id) INTO v_aseguradora FROM aseguradoras WHERE nombre=TRIM(p_aseguradora_nueva);
  IF v_aseguradora IS NULL THEN
    INSERT INTO aseguradoras(nombre) VALUES(TRIM(p_aseguradora_nueva));
    SET v_aseguradora=LAST_INSERT_ID();
  END IF;
 ELSE
  SELECT MIN(id) INTO v_aseguradora FROM aseguradoras WHERE id=p_aseguradora;
  IF v_aseguradora IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Aseguradora inexistente.'; END IF;
 END IF;
 IF p_modo='existente' THEN
  SELECT COUNT(*) INTO v_existe FROM poliza WHERE usuario_id=p_actor AND vehiculo_id=p_vehiculo;
  IF v_existe=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El vehiculo no pertenece a tus polizas.'; END IF;
  SET v_vehiculo=p_vehiculo;
 ELSEIF p_modo='nuevo' THEN
  IF TRIM(COALESCE(p_placas,''))='' OR TRIM(COALESCE(p_serie,''))='' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Faltan placas o serie del vehiculo.';
  END IF;
  INSERT INTO vehiculo(marca,modelo,color,placas,num_serie)
    VALUES(p_marca,p_modelo,p_color,p_placas,p_serie);
  SET v_vehiculo=LAST_INSERT_ID();
 ELSE
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Modo de vehiculo invalido.';
 END IF;
 SELECT COUNT(*) INTO v_existe FROM poliza WHERE num_poliza=p_numero AND aseguradora_id=v_aseguradora;
 IF v_existe>0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La poliza ya existe en esa aseguradora.'; END IF;
 INSERT INTO poliza(num_poliza,aseguradora_id,usuario_id,vehiculo_id)
  VALUES(p_numero,v_aseguradora,p_actor,v_vehiculo);
 SELECT LAST_INSERT_ID() AS poliza_id;
END$$

CREATE PROCEDURE sp_multimedia_guardar_ruta(IN p_tipo VARCHAR(10), IN p_ruta VARCHAR(255), IN p_caso INT UNSIGNED)
MODIFIES SQL DATA
BEGIN
 IF NOT EXISTS (SELECT 1 FROM siniestro WHERE id=p_caso) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Caso inexistente para evidencia.'; END IF;
 INSERT INTO multimedia(tipo,archivo,ruta,siniestro_id) VALUES(p_tipo,NULL,p_ruta,p_caso);
END$$
CREATE PROCEDURE sp_multimedia_metadatos(IN p_media INT UNSIGNED, IN p_actor INT UNSIGNED)
READS SQL DATA
BEGIN
 IF NOT EXISTS (SELECT 1 FROM multimedia m WHERE m.id=p_media AND fn_puede_ver_siniestro(p_actor,m.siniestro_id)=1) THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No tienes acceso a la evidencia.';
 END IF;
 SELECT m.id,m.tipo,m.ruta,m.siniestro_id,COALESCE(OCTET_LENGTH(m.archivo),0) AS bytes,
 SUBSTRING(m.archivo,1,65536) AS cabecera,s.ajustador_id,p.usuario_id AS Usuario
 FROM multimedia m JOIN siniestro s ON s.id=m.siniestro_id JOIN poliza p ON p.id=s.poliza_id WHERE m.id=p_media;
END$$
CREATE PROCEDURE sp_multimedia_bloque(IN p_media INT UNSIGNED, IN p_actor INT UNSIGNED, IN p_inicio INT, IN p_cantidad INT)
READS SQL DATA
BEGIN
 IF NOT EXISTS (SELECT 1 FROM multimedia m WHERE m.id=p_media AND fn_puede_ver_siniestro(p_actor,m.siniestro_id)=1) THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No tienes acceso a la evidencia.';
 END IF;
 SELECT SUBSTRING(archivo,p_inicio+1,LEAST(p_cantidad,1048576)) AS bloque FROM multimedia WHERE id=p_media;
END$$

-- La misma fila de siniestro se bloquea que en la reasignacion (transaccion PHP).
CREATE PROCEDURE sp_chat_contexto(IN p_caso INT UNSIGNED, IN p_actor INT UNSIGNED)
READS SQL DATA
BEGIN
 DECLARE v_id INT UNSIGNED DEFAULT NULL;
 SELECT id INTO v_id FROM siniestro WHERE id=p_caso FOR UPDATE;
 IF v_id IS NULL OR fn_puede_ver_siniestro(p_actor,p_caso)=0 THEN
   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No tienes acceso a este chat.';
 END IF;
 SELECT COALESCE(NULLIF(ua.alias,''),ua.nombre) AS asegurado,
  COALESCE(NULLIF(uj.alias,''),uj.nombre) AS ajustador
 FROM siniestro s JOIN poliza p ON p.id=s.poliza_id
 JOIN usuario ua ON ua.id=p.usuario_id LEFT JOIN usuario uj ON uj.id=s.ajustador_id
 WHERE s.id=p_caso;
END$$
CREATE PROCEDURE sp_chat_leer(IN p_caso INT UNSIGNED, IN p_actor INT UNSIGNED,
 IN p_antes INT UNSIGNED, IN p_despues INT UNSIGNED, IN p_limite INT)
READS SQL DATA
BEGIN
 DECLARE v_limite INT DEFAULT 51;
 SET v_limite=LEAST(GREATEST(COALESCE(p_limite,51),1),51);
 IF fn_puede_ver_siniestro(p_actor,p_caso)=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Acceso denegado al chat.'; END IF;
 SELECT c.id,c.usuario_id,c.mensaje,c.fechamensaje AS fecha,
 COALESCE(NULLIF(u.alias,''),u.nombre) AS autor,r.slug AS rol
 FROM chat c JOIN usuario u ON u.id=c.usuario_id JOIN rol r ON r.id=u.rol_id
 WHERE c.siniestro_id=p_caso AND (p_antes=0 OR c.id<p_antes) AND (p_despues=0 OR c.id>p_despues)
 ORDER BY CASE WHEN p_despues>0 THEN c.id END ASC, CASE WHEN p_despues=0 THEN c.id END DESC
 LIMIT v_limite;
END$$
CREATE PROCEDURE sp_chat_enviar(IN p_caso INT UNSIGNED, IN p_actor INT UNSIGNED, IN p_mensaje TEXT)
MODIFIES SQL DATA
BEGIN
 DECLARE v_id INT UNSIGNED DEFAULT NULL;
 SELECT id INTO v_id FROM siniestro WHERE id=p_caso FOR UPDATE;
 IF v_id IS NULL OR fn_puede_ver_siniestro(p_actor,p_caso)=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Acceso denegado al chat.'; END IF;
 IF TRIM(COALESCE(p_mensaje,''))='' OR CHAR_LENGTH(p_mensaje)>4000 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Mensaje invalido.'; END IF;
 INSERT INTO chat(mensaje,siniestro_id,usuario_id,mensaje_padre) VALUES(p_mensaje,p_caso,p_actor,NULL);
 SELECT LAST_INSERT_ID() AS mensaje_id;
END$$
DELIMITER ;

-- Auditoria rapida de objetos, sin modificar datos:
-- SHOW TRIGGERS LIKE 'siniestro';
-- SHOW FUNCTION STATUS WHERE Db='bdm_aseguradora';
-- SHOW PROCEDURE STATUS WHERE Db='bdm_aseguradora';

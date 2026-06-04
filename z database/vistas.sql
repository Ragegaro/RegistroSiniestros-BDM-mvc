create or replace view vLogIn AS
select
	u.id,
    u.alias,
    u.contrasena,
    u.rol_id,
    r.nombre AS NombreRol,
    r.slug
    From usuario u
    join rol r on u.rol_id = r.id;
    
CREATE OR REPLACE VIEW vPolizas AS 
SELECT 
	p.id, 
	p.usuario_id AS Usuario,
    u.nombre AS Cliente,
    p.num_poliza AS Poliza,
	v.Marca,
	v.Modelo,
	a.nombre AS Aseguradora
FROM poliza p
JOIN  vehiculo v ON p.vehiculo_id = v.id
JOIN  aseguradoras a ON p.aseguradora_id = a.id
JOIN usuario u ON p.usuario_id=u.id;


CREATE OR REPLACE VIEW vListarSiniestros AS 
SELECT 
    s.id,
    s.fecha,
    s.hora,
    s.direccion,
    s.descripcion,
    vp.Poliza,
    vp.Usuario,
    vp.Cliente AS nombreCliente,
    s.ajustador_id,
    vp.Marca,
    vp.Modelo,
    vp.Aseguradora,
    IFNULL (e.nombre,'Sin Estatus') AS Estatus,
    IFNULL(u_ajustador.nombre, 'No Asignado') AS Ajustador
FROM siniestro s
JOIN vPolizas vp ON s.poliza_id = vp.id
LEFT JOIN estatus e ON s.estatus_id = e.id
LEFT JOIN usuario u_ajustador ON s.ajustador_id = u_ajustador.id;


CREATE OR REPLACE VIEW vHistorialEstatus AS
SELECT 
    hs.siniestro_id,
    e.nombre AS Estatus_Nuevo,
    hs.fecha_actu AS Fecha_Cambio
-- En  un futuro agregar quien genero cambio
--    u.nombre AS Modificado_Por,
--   r.slug AS Rol_Usuario
FROM historialestatus hs
JOIN estatus e ON hs.estatus_id = e.id
order by hs.fecha_actu desc;
-- LEFT JOIN usuario u ON hs.usuario_id = u.id
-- LEFT JOIN rol r ON u.rol_id = r.id;


CREATE OR REPLACE VIEW vListarClientes AS
SELECT 
    u.id AS Cliente_ID,
    CONCAT(u.nombre, ' ', u.apellido_p, ' ', u.apellido_m) AS Nombre_Completo,
    u.email,
    COUNT(DISTINCT p.id) AS Total_Polizas,
    COUNT(DISTINCT s.id) AS Total_Siniestros
FROM usuario u
JOIN rol r ON u.rol_id = r.id
LEFT JOIN poliza p ON u.id = p.usuario_id
LEFT JOIN siniestro s ON p.id = s.poliza_id
WHERE r.slug = 'asegurado'
GROUP BY u.id, u.nombre, u.apellido_p, u.apellido_m, u.email;


CREATE OR REPLACE VIEW vListarAjustadores AS
SELECT 
    u.id AS Ajustador_ID,
    CONCAT(u.nombre, ' ', u.apellido_p) AS Nombre_Ajustador,
    COUNT(s.id) AS Siniestros_Asignados
FROM usuario u
JOIN rol r ON u.rol_id = r.id
LEFT JOIN siniestro s ON u.id = s.ajustador_id
WHERE r.slug = 'ajustador'
GROUP BY u.id, u.nombre, u.apellido_p;

CREATE OR REPLACE VIEW vBusquedaAvanzada AS
SELECT 
    s.id AS Folio_Siniestro,
    p.num_poliza AS Numero_Poliza,
    CONCAT(u.nombre, ' ', u.apellido_p) AS Cliente,
    v.Marca,
    v.Modelo,
    v.Placas,
    e.nombre AS Estatus_Actual,
    s.fecha AS Fecha_Siniestro
FROM siniestro s
JOIN poliza p ON s.poliza_id = p.id
JOIN vehiculo v ON p.vehiculo_id = v.id
JOIN usuario u ON p.usuario_id = u.id
LEFT JOIN estatus e ON s.estatus_id = e.id;

CREATE OR REPLACE VIEW vDashboardAdmin AS
SELECT 
    e.nombre AS Estatus_Siniestro,
    COUNT(s.id) AS Cantidad_Total
FROM estatus e
LEFT JOIN siniestro s ON e.id = s.estatus_id
GROUP BY e.id, e.nombre;


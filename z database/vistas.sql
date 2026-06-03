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


-- CReate or replace view vHistorialSegumiento AS

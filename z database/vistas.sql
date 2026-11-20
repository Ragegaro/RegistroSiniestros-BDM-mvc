USE bdm_aseguradora;

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

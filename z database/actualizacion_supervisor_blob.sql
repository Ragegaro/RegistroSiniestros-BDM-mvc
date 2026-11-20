-- ACTUALIZACION PARA UNA BASE EXISTENTE. Hacer respaldo antes de importar.
-- No borra usuarios, evidencias ni asignaciones actuales.
USE bdm_aseguradora;

-- El supervisor asignara los nuevos reportes mediante el menu de la aplicacion.
-- Se retira solamente el disparador identificado en el ZIP original, que
-- seleccionaba un ajustador al azar antes de insertar cada siniestro.
DROP TRIGGER IF EXISTS bdm_aseguradora.T_asignar_Ajust;

-- multimedia.archivo YA es LONGBLOB en el esquema de este proyecto.
-- La conversion de videos del disco se hace desde el menu Videos anteriores;
-- SQL por si solo no dispone de los archivos del servidor PHP.
SHOW COLUMNS FROM bdm_aseguradora.multimedia LIKE 'archivo';

-- Debe devolver archivo / longblob. Consulta de control de almacenamiento:
SELECT id, siniestro_id, tipo, OCTET_LENGTH(archivo) AS bytes_en_blob,
       CASE WHEN COALESCE(OCTET_LENGTH(archivo), 0) > 0 THEN 'BLOB'
            ELSE 'Pendiente de convertir' END AS almacenamiento
FROM bdm_aseguradora.multimedia WHERE tipo = 'Video' ORDER BY id;

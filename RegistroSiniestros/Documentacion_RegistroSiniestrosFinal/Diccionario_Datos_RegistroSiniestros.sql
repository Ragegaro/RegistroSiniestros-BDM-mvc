/*
  DICCIONARIO DE DATOS CONSULTABLE - REGISTRO DE SINIESTROS
  Destino: base existente bdm_aseguradora, en MySQL/MariaDB de XAMPP.
  Codificacion: UTF-8. No es una reinstalacion de la base de datos.

  COMO INSTALAR
  1. En phpMyAdmin selecciona bdm_aseguradora.
  2. Abre Importar, selecciona este archivo y pulsa Continuar/Importar.
  3. Actualiza el panel izquierdo y abre diccionario_datos > Examinar.
     Segun la version de phpMyAdmin puede aparecer dentro de Vistas.

  QUE CREA
  - diccionario_datos: una fila por campo de cada tabla de la base.
  - diccionario_tablas: resumen y descripcion de cada tabla.
  - diccionario_relaciones: claves foraneas y sus reglas.

  SEGURIDAD Y ALCANCE
  Solo crea o reemplaza estas tres vistas de documentacion. No modifica,
  borra ni copia registros de usuarios, polizas, siniestros, chats o BLOB.
  No cambia tipos de columnas, contrasenas, claves ni permisos.
  Si ya existen vistas con estos tres nombres, reemplaza SUS definiciones.
  Si alguno de esos nombres pertenece a una tabla, no la borres para importar:
  cambia el nombre de la vista de documentacion en este archivo.
  Conserva cualquier vista antigua llamada DICCIONARIO.

  Los tipos, NULL, valores predeterminados y claves se leen de la estructura
  ACTUAL mediante INFORMATION_SCHEMA. Si agregas una columna, aparecera al
  consultar otra vez. Las vistas SQL no se incluyen como si fueran tablas.
  Las descripciones funcionales corresponden al proyecto final; no se
  generan solas para campos nuevos. Un COMMENT existente tiene prioridad.
  La informacion visible depende de los permisos del usuario SQL que consulta.
  Para instalar se necesitan CREATE VIEW y los permisos de lectura pertinentes;
  OR REPLACE tambien requiere DROP sobre las vistas de documentacion.
  El archivo no concede permisos ni configura cuentas.

  IMPORTANTE
  - Si tu base tiene otro nombre, sustituye TODAS las apariciones de
    bdm_aseguradora en el archivo, no solamente la instruccion USE.
  - restricciones_unique muestra nombres de restricciones UNIQUE distintas
    de la PK. La misma restriccion en varios campos indica una clave compuesta:
    no significa que cada campo sea unico por separado.
  - valor_predeterminado conserva COLUMN_DEFAULT tal como lo informa el
    servidor; no confunde un NULL SQL con el texto 'NULL'.
  - relaciones: una fila por columna de una FK, con su posicion. En el esquema
    final las 15 FK son de una sola columna; una FK compuesta tendria varias.
  - Es documentacion de estructura, NO contiene las contrasenas de las cuentas.

  Referencias tecnicas de la implementacion:
  https://mariadb.com/docs/server/server-usage/views/create-view
  https://mariadb.com/docs/server/reference/system-tables/information-schema/information-schema-tables/information-schema-columns-table
  https://mariadb.com/docs/server/reference/system-tables/information-schema/information-schema-tables/information-schema-key_column_usage-table
*/

USE `bdm_aseguradora`;
SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

-- 1. Diccionario principal. No usa COLUMN_KEY para inferir las FK:
--    las claves se consultan en los catalogos de restricciones reales.
CREATE OR REPLACE ALGORITHM = UNDEFINED SQL SECURITY INVOKER
VIEW `bdm_aseguradora`.`diccionario_datos` AS
SELECT
    c.TABLE_NAME AS tabla,
    c.ORDINAL_POSITION AS orden,
    c.COLUMN_NAME AS campo,
    c.COLUMN_TYPE AS tipo_dato,
    CASE WHEN c.IS_NULLABLE = 'YES' THEN 'Si' ELSE 'No' END AS permite_null,
    c.COLUMN_DEFAULT AS valor_predeterminado,
    CASE WHEN EXISTS (
        SELECT 1
        FROM information_schema.KEY_COLUMN_USAGE AS pk
        WHERE pk.TABLE_SCHEMA = c.TABLE_SCHEMA
          AND pk.TABLE_NAME = c.TABLE_NAME
          AND pk.COLUMN_NAME = c.COLUMN_NAME
          AND pk.CONSTRAINT_NAME = 'PRIMARY'
    ) THEN 'Si' ELSE 'No' END AS clave_primaria,
    COALESCE((
        SELECT GROUP_CONCAT(uk.CONSTRAINT_NAME SEPARATOR ', ')
        FROM information_schema.KEY_COLUMN_USAGE AS uk
        INNER JOIN information_schema.TABLE_CONSTRAINTS AS tc
          ON tc.CONSTRAINT_SCHEMA = uk.CONSTRAINT_SCHEMA
         AND tc.CONSTRAINT_NAME = uk.CONSTRAINT_NAME
         AND tc.TABLE_SCHEMA = uk.TABLE_SCHEMA
         AND tc.TABLE_NAME = uk.TABLE_NAME
        WHERE uk.TABLE_SCHEMA = c.TABLE_SCHEMA
          AND uk.TABLE_NAME = c.TABLE_NAME
          AND uk.COLUMN_NAME = c.COLUMN_NAME
          AND tc.CONSTRAINT_TYPE = 'UNIQUE'
    ), '') AS restricciones_unique,
    COALESCE((
        SELECT GROUP_CONCAT(
            CONCAT(fk.CONSTRAINT_NAME, ': ', fk.REFERENCED_TABLE_SCHEMA,
                   '.', fk.REFERENCED_TABLE_NAME, '.', fk.REFERENCED_COLUMN_NAME)
            SEPARATOR '; '
        )
        FROM information_schema.KEY_COLUMN_USAGE AS fk
        WHERE fk.TABLE_SCHEMA = c.TABLE_SCHEMA
          AND fk.TABLE_NAME = c.TABLE_NAME
          AND fk.COLUMN_NAME = c.COLUMN_NAME
          AND fk.REFERENCED_TABLE_NAME IS NOT NULL
    ), '') AS referencias_fk,
    c.EXTRA AS extra,
    COALESCE(NULLIF(c.COLUMN_COMMENT, ''),
        CASE CONCAT(c.TABLE_NAME, '.', c.COLUMN_NAME)
            WHEN 'rol.id' THEN 'Identificador del rol.'
            WHEN 'rol.nombre' THEN 'Nombre visible del rol.'
            WHEN 'rol.slug' THEN 'Identificador del rol utilizado por PHP para comprobar permisos.'

            WHEN 'usuario.id' THEN 'Identificador de la cuenta.'
            WHEN 'usuario.nombre' THEN 'Nombre de la persona.'
            WHEN 'usuario.apellido_p' THEN 'Apellido paterno.'
            WHEN 'usuario.apellido_m' THEN 'Apellido materno.'
            WHEN 'usuario.nacimiento' THEN 'Fecha de nacimiento.'
            WHEN 'usuario.genero' THEN 'Genero declarado por la persona.'
            WHEN 'usuario.email' THEN 'Correo electronico de la cuenta.'
            WHEN 'usuario.contrasena' THEN 'Valor de la contrasena procesado por la aplicacion; no es el texto de acceso legible.'
            WHEN 'usuario.alias' THEN 'Nombre de usuario utilizado para iniciar sesion.'
            WHEN 'usuario.foto_perfil' THEN 'Contenido binario de la imagen de perfil.'
            WHEN 'usuario.estatus' THEN 'Actividad de la cuenta: PHP permite acceso cuando vale 1. No es el estatus del siniestro.'
            WHEN 'usuario.rol_id' THEN 'Rol al que pertenece la cuenta.'

            WHEN 'aseguradoras.id' THEN 'Identificador de la compania aseguradora.'
            WHEN 'aseguradoras.nombre' THEN 'Nombre de la compania que emite la poliza.'

            WHEN 'vehiculo.id' THEN 'Identificador del vehiculo.'
            WHEN 'vehiculo.marca' THEN 'Marca del vehiculo.'
            WHEN 'vehiculo.modelo' THEN 'Modelo del vehiculo.'
            WHEN 'vehiculo.color' THEN 'Color del vehiculo.'
            WHEN 'vehiculo.placas' THEN 'Matricula o placas del vehiculo.'
            WHEN 'vehiculo.num_serie' THEN 'Numero de serie del vehiculo.'

            WHEN 'poliza.id' THEN 'Identificador de la poliza.'
            WHEN 'poliza.num_poliza' THEN 'Numero de la poliza emitida por la aseguradora.'
            WHEN 'poliza.aseguradora_id' THEN 'Compania emisora de la poliza.'
            WHEN 'poliza.usuario_id' THEN 'Cuenta del titular de la poliza.'
            WHEN 'poliza.vehiculo_id' THEN 'Vehiculo cubierto por la poliza.'

            WHEN 'estatus.id' THEN 'Identificador del estado del siniestro.'
            WHEN 'estatus.nombre' THEN 'Nombre del estado en el catalogo de siniestros.'

            WHEN 'siniestro.id' THEN 'Identificador o folio del expediente.'
            WHEN 'siniestro.fecha' THEN 'Fecha en que ocurrio el siniestro.'
            WHEN 'siniestro.hora' THEN 'Hora en que ocurrio el siniestro.'
            WHEN 'siniestro.direccion' THEN 'Ubicacion donde ocurrio el siniestro.'
            WHEN 'siniestro.descripcion' THEN 'Descripcion de lo ocurrido.'
            WHEN 'siniestro.fecha_registro' THEN 'Fecha y hora en que se registro el expediente.'
            WHEN 'siniestro.ajustador_id' THEN 'Cuenta del ajustador actualmente asignado al caso.'
            WHEN 'siniestro.poliza_id' THEN 'Poliza a la que pertenece el siniestro.'
            WHEN 'siniestro.estatus_id' THEN 'Estado actual del expediente.'

            WHEN 'historialestatus.id' THEN 'Identificador del cambio de estado.'
            WHEN 'historialestatus.fecha_actu' THEN 'Fecha y hora del cambio de estado.'
            WHEN 'historialestatus.siniestro_id' THEN 'Siniestro cuyo estado cambio.'
            WHEN 'historialestatus.estatus_id' THEN 'Nuevo estado registrado en el historial.'

            WHEN 'multimedia.id' THEN 'Identificador de la evidencia.'
            WHEN 'multimedia.tipo' THEN 'Clasificacion de la evidencia: fotografia o video.'
            WHEN 'multimedia.archivo' THEN 'Contenido binario de la fotografia o del video del expediente.'
            WHEN 'multimedia.ruta' THEN 'Ruta conservada para compatibilidad con evidencias antiguas guardadas como archivos.'
            WHEN 'multimedia.fecha' THEN 'Fecha y hora en que se cargo la evidencia.'
            WHEN 'multimedia.siniestro_id' THEN 'Expediente al que pertenece la evidencia.'

            WHEN 'evaluacion.id' THEN 'Identificador de la evaluacion.'
            WHEN 'evaluacion.diagnostico' THEN 'Texto del diagnostico del siniestro.'
            WHEN 'evaluacion.fecha_evaluacion' THEN 'Fecha y hora de la evaluacion o su ultima actualizacion por PHP.'
            WHEN 'evaluacion.siniestro_id' THEN 'Siniestro evaluado.'
            WHEN 'evaluacion.ajustador_id' THEN 'Cuenta que guarda el diagnostico; el flujo tambien permite actuar al supervisor.'

            WHEN 'chat.id' THEN 'Identificador del mensaje.'
            WHEN 'chat.mensaje' THEN 'Texto del mensaje. La aplicacion valida un maximo de 2000 caracteres.'
            WHEN 'chat.fechamensaje' THEN 'Fecha y hora en que se envio el mensaje.'
            WHEN 'chat.siniestro_id' THEN 'Siniestro cuya conversacion contiene el mensaje.'
            WHEN 'chat.usuario_id' THEN 'Cuenta autora del mensaje.'
            WHEN 'chat.mensaje_padre' THEN 'Mensaje al que se responde. El envio actual de la aplicacion deja este campo en NULL.'
            ELSE 'Campo sin descripcion funcional registrada.'
        END
    ) AS descripcion
FROM information_schema.COLUMNS AS c
INNER JOIN information_schema.TABLES AS t
  ON t.TABLE_SCHEMA = c.TABLE_SCHEMA
 AND t.TABLE_NAME = c.TABLE_NAME
WHERE c.TABLE_SCHEMA = 'bdm_aseguradora'
  AND t.TABLE_TYPE = 'BASE TABLE'
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION;

-- 2. Resumen. Cuenta campos de metadatos, no registros de personas o casos.
CREATE OR REPLACE ALGORITHM = UNDEFINED SQL SECURITY INVOKER
VIEW `bdm_aseguradora`.`diccionario_tablas` AS
SELECT
    t.TABLE_NAME AS tabla,
    (SELECT COUNT(*)
     FROM information_schema.COLUMNS AS c
     WHERE c.TABLE_SCHEMA = t.TABLE_SCHEMA
       AND c.TABLE_NAME = t.TABLE_NAME) AS cantidad_campos,
    t.ENGINE AS motor,
    t.TABLE_COLLATION AS cotejamiento,
    COALESCE(NULLIF(t.TABLE_COMMENT, ''),
        CASE t.TABLE_NAME
            WHEN 'rol' THEN 'Catalogo de roles de las cuentas: asegurado, ajustador y supervisor.'
            WHEN 'usuario' THEN 'Cuentas, datos personales, acceso y fotografias de perfil.'
            WHEN 'aseguradoras' THEN 'Companias emisoras de las polizas.'
            WHEN 'vehiculo' THEN 'Vehiculos cubiertos por las polizas.'
            WHEN 'poliza' THEN 'Relacion entre titular, compania aseguradora y vehiculo.'
            WHEN 'estatus' THEN 'Catalogo de estados de los siniestros.'
            WHEN 'siniestro' THEN 'Expediente principal, poliza, ajustador asignado y estado actual.'
            WHEN 'historialestatus' THEN 'Historial de los cambios de estado de cada expediente.'
            WHEN 'multimedia' THEN 'Fotografias y videos asociados a cada siniestro.'
            WHEN 'evaluacion' THEN 'Diagnostico del siniestro y cuenta que lo registra.'
            WHEN 'chat' THEN 'Mensajes agrupados por siniestro; los participantes autorizados los determina PHP.'
            ELSE 'Tabla sin descripcion funcional registrada.'
        END
    ) AS descripcion
FROM information_schema.TABLES AS t
WHERE t.TABLE_SCHEMA = 'bdm_aseguradora'
  AND t.TABLE_TYPE = 'BASE TABLE'
ORDER BY t.TABLE_NAME;

-- 3. Relaciones reales. Incluye el nombre de la restriccion y las reglas
--    efectivas de la instancia, incluso si el DDL original no las escribia.
CREATE OR REPLACE ALGORITHM = UNDEFINED SQL SECURITY INVOKER
VIEW `bdm_aseguradora`.`diccionario_relaciones` AS
SELECT
    k.TABLE_NAME AS tabla,
    k.CONSTRAINT_NAME AS restriccion_fk,
    k.ORDINAL_POSITION AS posicion_en_fk,
    k.COLUMN_NAME AS campo,
    k.REFERENCED_TABLE_SCHEMA AS base_referenciada,
    k.REFERENCED_TABLE_NAME AS tabla_referenciada,
    k.REFERENCED_COLUMN_NAME AS campo_referenciado,
    CASE WHEN c.IS_NULLABLE = 'YES' THEN 'Si' ELSE 'No' END AS permite_null,
    r.UPDATE_RULE AS al_actualizar,
    r.DELETE_RULE AS al_eliminar
FROM information_schema.KEY_COLUMN_USAGE AS k
INNER JOIN information_schema.COLUMNS AS c
  ON c.TABLE_SCHEMA = k.TABLE_SCHEMA
 AND c.TABLE_NAME = k.TABLE_NAME
 AND c.COLUMN_NAME = k.COLUMN_NAME
INNER JOIN information_schema.TABLES AS t
  ON t.TABLE_SCHEMA = k.TABLE_SCHEMA
 AND t.TABLE_NAME = k.TABLE_NAME
LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS AS r
  ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
 AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
 AND r.TABLE_NAME = k.TABLE_NAME
WHERE k.TABLE_SCHEMA = 'bdm_aseguradora'
  AND t.TABLE_TYPE = 'BASE TABLE'
  AND k.REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION;

-- Comprobacion al importar. Con el esquema final sin tablas adicionales:
-- 11 tablas, 60 campos y 15 relaciones de una columna. Si tu estructura
-- cambio, los conteos reflejaran esa estructura, no numeros forzados.
SELECT
    (SELECT COUNT(*) FROM `bdm_aseguradora`.`diccionario_tablas`) AS tablas_detectadas,
    (SELECT COUNT(*) FROM `bdm_aseguradora`.`diccionario_datos`) AS campos_detectados,
    (SELECT COUNT(*) FROM `bdm_aseguradora`.`diccionario_relaciones`) AS columnas_en_fk;

/* CONSULTAS LISTAS PARA COPIAR EN LA PESTANA SQL DE PHPMYADMIN

-- Ver el diccionario completo:
SELECT * FROM bdm_aseguradora.diccionario_datos ORDER BY tabla, orden;

-- Ver solamente una tabla:
SELECT * FROM bdm_aseguradora.diccionario_datos
WHERE tabla = 'usuario' ORDER BY orden;

-- Ver los campos multimedia:
SELECT * FROM bdm_aseguradora.diccionario_datos
WHERE tabla = 'multimedia' ORDER BY orden;

-- Ver las relaciones:
SELECT * FROM bdm_aseguradora.diccionario_relaciones
ORDER BY tabla, restriccion_fk, posicion_en_fk;

-- Ver el resumen de tablas:
SELECT * FROM bdm_aseguradora.diccionario_tablas ORDER BY tabla;

-- Ver los miembros de una restriccion UNIQUE compuesta, en orden:
SELECT TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION, COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'bdm_aseguradora'
  AND TABLE_NAME = 'poliza'
  AND CONSTRAINT_NAME = 'uq_poliza_aseguradora'
ORDER BY ORDINAL_POSITION;

*/

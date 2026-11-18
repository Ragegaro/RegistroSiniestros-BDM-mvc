use bdm_aseguradora;
SHOW tables;

SELECT * FROM DICCIONARIO;

SELECT 
    TABLE_NAME AS 'Tabla', 
    COLUMN_NAME AS 'Campo', 
    COLUMN_TYPE AS 'Tipo de Dato', 
    IS_NULLABLE AS 'Nulo', 
    COLUMN_KEY AS 'Llave', 
    COLUMN_DEFAULT AS 'Predeterminado', 
    EXTRA AS 'Extra (AI)',
    COLUMN_COMMENT AS 'Descripción'
FROM 
    information_schema.columns 
WHERE 
    table_schema = 'bdm_aseguradora' 
ORDER BY 
    TABLE_NAME, ORDINAL_POSITION;
    
    
    
    
    
CREATE VIEW DICCIONARIO
AS
SELECT distinct
       t.table_name,
       c.ordinal_position,
       (CASE WHEN t.table_type = 'BASE TABLE' THEN 'table'
             WHEN t.table_type = 'VIEW' THEN 'view'
             ELSE t.table_type
        END) AS table_type,
        c.column_name,
        c.column_type,
        c.column_default,
        c.column_key,
        c.is_nullable,
        c.extra,
        c.column_comment
FROM information_schema.tables AS t
INNER JOIN information_schema.columns AS c
ON t.table_name = c.table_name
AND t.table_schema = c.table_schema
WHERE t.table_type IN ('BASE TABLE', 'VIEW')
AND t.table_schema = 'bdm_aseguradora'
ORDER BY
		t.table_name,
		c.ordinal_position;
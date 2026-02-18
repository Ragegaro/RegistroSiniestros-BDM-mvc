CREATE TABLE bdm_aseguradora;
USE bdm_aseguradora;

CREATE TABLE Usuario(
    ID INT(11) PRIMARY KEY AUTO_INCREMENT ,AUTO_INCREMENT=30, 
    nombre VARCHAR (50)NOT NULL,
    apellidoP VARCHAR (50)NOT NULL,
    apellidoM VARCHAR (50),
    nacimiento date NOT NULL,
    foto BLOB,
    genero VARCHAR(50),
    correo VARCHAR(100)NOT NULL,
    contraseña VARCHAR(255)NOT NULL,
    alias VARCHAR (50)NOT NULL,
    userType varchar(50) default 'Asegurado' NOT NULL
);


create table rol(
	id tinyint unsigned  auto_increment primary key,
	nombre varchar(50) not null,
    es_default tinyint(1) DEFAULT 0
    
);
create table usuario (
    id  INT(11) unsigned auto_increment primary key , 
    nombre VARCHAR (50)NOT NULL,
    apellido_p VARCHAR (50)NOT NULL,
    apellido_m VARCHAR (50),
    nacimiento date NOT NULL,
    genero VARCHAR(20),
    email VARCHAR(100) NOT NULL UNIQUE,
	contrasena VARCHAR(255)NOT NULL,
	alias VARCHAR (50)NOT NULL,
    foto_perfil BLOB,
    estatus tinyint unsigned default (1),
    
    rol_id tinyint unsigned not null,
    foreign key (rol_id) references rol (id)
);

create table aseguradoras(
	id int unsigned primary key auto_increment,
	nombre varchar(100) not null
);
create table poliza(
	id int unsigned auto_increment primary key,
    num_poliza varchar(50) not null,
    
    aseguradora_id int unsigned not null,
    usuario_id int unsigned not null,
    vehiculo_id int unsigned not null,
    unique (num_poliza,aseguradora_id),
    foreign key (aseguradora_id) references aseguradoras(id),
    foreign key (usuario_id) references usuario(id),
    foreign key (vehiculo_id) references vehiculo(id)
);
create table vehiculo(
	id int unsigned primary key auto_increment,
    marca varchar(30) not null,
    modelo varchar(50)not null,
    color varchar(30),
    placas varchar (10) not null unique,
    num_serie varchar(50) not null unique,
    poliza_id int unsigned,
    foreign key (poliza_id) references poliza (id)
);

create table estatus( 
	id tinyint unsigned primary key auto_increment,
	nombre varchar (100) not null
);
create table siniestro(
	id int unsigned primary key auto_increment,
	fecha date not null,
	hora time not null,
    direccion text not null,
    descripcion text,
    fecha_registro timestamp default current_timestamp,
    ajustador_id int unsigned not null,
    poliza_id int  unsigned not null,
    estatus_id tinyint unsigned not null,
    foreign key (poliza_id) references poliza(id),
    foreign key (ajustador_id) references usuario (id),
	foreign key (estatus_id) references estatus(id)
);
create table historialestatus(
	id int unsigned primary key auto_increment,
    fecha_actu timestamp default current_timestamp,
    siniestro_id int unsigned not null,
    estatus_id tinyint unsigned not null,
    foreign key (siniestro_id) references siniestro(id),
    foreign key (estatus_id) references estatus (id)    
    );
create table multimedia(
	id int unsigned primary key auto_increment,
    tipo enum ('Foto',"Video")not null,
    archivo blob null,
    ruta varchar(255) null,
    fecha timestamp default current_timestamp,
    siniestro_id int unsigned not null,
    foreign key (siniestro_id) references siniestro(id)
);
create table chat(
	id int unsigned primary key auto_increment,
	mensaje text not null,
	fechamensaje timestamp default current_timestamp,
	siniestro_id int unsigned not null,
	usuario_id int unsigned not null,
    mensaje_padre int unsigned null,    
    
    foreign key (siniestro_id) references siniestro (id),
    foreign key (usuario_id) references usuario (id),
    foreign key (message_padre) references chat (id)    
);
 ----------------------------------------
 
/*
alter table usuario add column  estatus tinyint unsigned default (1)
alter table siniestro add column poliza_id int unsigned not null
ALTER TABLE siniestro add constraint fk_siniestro_poliza foreign key(poliza_id)references poliza(id);
alter table  siniestro drop column vehiculo_id
*/
-----------------------------

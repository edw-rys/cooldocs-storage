DROP DATABASE IF EXISTS prueba_telco_users;
create database prueba_telco_users;
create table settings_api(
    id bigint(20) UNSIGNED auto_increment,
    url text not null,
    load_api smallint(1) DEFAULT 1,
    created_at datetime not null,
    update_at datetime DEFAULT null,
    deleted_at datetime DEFAULT null,
	PRIMARY KEY (id)
)ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usuarios
create table users(
    id bigint(20) UNSIGNED auto_increment,
    first_name varchar(500) not null,
    last_name varchar(500) not null,
    age int(5) not null,
    email varchar(500) not null,
    image varchar(500) DEFAULT null,
    external_image smallint(1) DEFAULT 1,
    password text DEFAULT 'no',
    status varchar(25) DEFAULT 'active',
    type varchar(25) DEFAULT 'client',
    created_at datetime not null,
    update_at datetime DEFAULT null,
    deleted_at datetime DEFAULT null,
	PRIMARY KEY (id)
)ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
insert into users (id, first_name, last_name, age, email, image, password, status, type, created_at) values (1, 'Edw', 'Rys', '24', 'edw-toni@hotmail.com', null, '$2y$11$UqaZgzXhPcifCa66RkYtXOrrMoAKZR8gXKRQ1r6kPmcbfhIZkCt/2', 'active', 'admin', '2023-03-30 11:26:01');

create table especialidad(
    id bigint(20) UNSIGNED auto_increment,
	name varchar(500) not null,
    status varchar(25) DEFAULT 'active',
    created_at datetime not null,
    update_at datetime DEFAULT null,
    deleted_at datetime DEFAULT null,
	PRIMARY KEY (id)
)ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
insert into especialidad (name, created_at) values ('Medicina General','2022-03-30 10:00:00'), ('Nutricionista','2022-03-30 10:00:00'), ('Odontología','2022-03-30 10:00:00');



create table cita(
    id bigint(20) UNSIGNED auto_increment,
    especialidad_id bigint(20) UNSIGNED NOT NULL,
    user_id bigint(20) UNSIGNED NOT NULL,
	date_complete datetime not null,
	date_cita date not null,
	time_cita time not null,
    observation text DEFAULT null,
    status varchar(25) DEFAULT 'active',
    created_at datetime not null,
    update_at datetime DEFAULT null,
    deleted_at datetime DEFAULT null,
	PRIMARY KEY (id)
)ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

Alter table cita 
    add KEY cita_especialidad_id (`especialidad_id`),
    add KEY cita_user_id (`user_id`);

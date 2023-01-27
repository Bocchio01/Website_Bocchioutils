<?php
include "../_setting.php";

$sql = array();
$sql[] = "DROP TABLE IF EXISTS PLM_Taxes_Payments";
$sql[] = "DROP TABLE IF EXISTS PLM_Lessons_List";
$sql[] = "DROP TABLE IF EXISTS PLM_Lessons_Payments";
$sql[] = "DROP TABLE IF EXISTS PLM_Alumni";
$sql[] = "DROP TABLE IF EXISTS PLM_Professor";

$sql[] = "CREATE TABLE IF NOT EXISTS PLM_Professor (
    id INT(4) NOT NULL AUTO_INCREMENT,
    id_professor INT(4),
    token VARCHAR(127) NOT NULL,
    approved BOOLEAN DEFAULT 0,

    UNIQUE (token),
    PRIMARY KEY (id),
    FOREIGN KEY (id_professor) REFERENCES BWS_Users(id_user) ON DELETE SET NULL ON UPDATE CASCADE)
    ENGINE=InnoDB";



// All alumni table (associated to professor ID and payment per hour)
$sql[] = "CREATE TABLE IF NOT EXISTS PLM_Alumni (
    id_alumno INT(4) NOT NULL AUTO_INCREMENT,
    id_owner INT(4),
    id_auth_professors JSON DEFAULT NULL,
    name VARCHAR(127) NOT NULL,
    surname VARCHAR(127) DEFAULT NULL,
    email VARCHAR(127) DEFAULT NULL,

    default_price DECIMAL(4, 2) DEFAULT 0,
    default_extra DECIMAL(4, 2) DEFAULT 0,

    entry_password VARCHAR(127) NOT NULL,

    UNIQUE (entry_password),
    PRIMARY KEY (id_alumno),
    FOREIGN KEY (id_owner) REFERENCES PLM_Professor(id) ON DELETE SET NULL ON UPDATE CASCADE)
    ENGINE=InnoDB";


// All lessons and their details
$sql[] = "CREATE TABLE IF NOT EXISTS PLM_Lessons_List (
    id INT(4) NOT NULL AUTO_INCREMENT,
    id_alumno INT(4),
    id_professor INT(4),

    minutes INT(4) DEFAULT 0,
    price DECIMAL(6, 2) DEFAULT 0,
    extra DECIMAL(6, 2) DEFAULT 0,
    subject JSON DEFAULT NULL,
    arguments LONGTEXT NULL,

    date_lessons DATE NOT NULL,
    last_modify TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    FOREIGN KEY (id_alumno) REFERENCES PLM_Alumni(id_alumno) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_professor) REFERENCES PLM_Professor(id) ON DELETE SET NULL ON UPDATE CASCADE)
    ENGINE=InnoDB";


// All payments received from students and their detail (also the professor that received money)
$sql[] = "CREATE TABLE IF NOT EXISTS PLM_Lessons_Payments (
    id INT(4) NOT NULL AUTO_INCREMENT,
    id_alumno INT(4),
    id_professor INT(4),

    amount DECIMAL(6, 2) DEFAULT 0,
    date_received DATE NOT NULL,
    last_modify TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    type ENUM('Contanti', 'Bonifico') DEFAULT 'Contanti',
    location VARCHAR(127) DEFAULT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (id_alumno) REFERENCES PLM_Alumni(id_alumno) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_professor) REFERENCES PLM_Professor(id) ON DELETE SET NULL ON UPDATE CASCADE)
    ENGINE=InnoDB";


// All payments of taxes from professor to
$sql[] = "CREATE TABLE IF NOT EXISTS PLM_Taxes_Payments (
    id INT(4) NOT NULL AUTO_INCREMENT,
    id_professor INT(4),
    id_owner INT(4),

    amount DECIMAL(6, 2) DEFAULT 0,
    date_received DATE NOT NULL,
    last_modify TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    type ENUM('Contanti', 'Bonifico') DEFAULT 'Contanti',
    location VARCHAR(127) DEFAULT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (id_owner) REFERENCES PLM_Professor(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_professor) REFERENCES PLM_Professor(id) ON DELETE SET NULL ON UPDATE CASCADE)
    ENGINE=InnoDB";



foreach ($sql as $value) Query($value);

$conn->close();
returndata(0, "Connection with MySQL database closed");

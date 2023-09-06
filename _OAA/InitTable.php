<?php
include "../_setting.php";

$sql = array();


// OAA_Users table
$sql[] = "CREATE TABLE IF NOT EXISTS OAA_Users (
    id_user INT(4) NOT NULL AUTO_INCREMENT,
    name VARCHAR(127) DEFAULT NULL,
    surname VARCHAR(127) DEFAULT NULL,
    phone VARCHAR(127) DEFAULT NULL,
    email VARCHAR(127) NOT NULL,
    password VARCHAR(127) NOT NULL,

    permission_code INT(2) NOT NULL,

    events_access JSON DEFAULT ('[]'),

    token VARCHAR(127) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (email),
    PRIMARY KEY (id_user))
    ENGINE=InnoDB";


// OAA_Maps table
$sql[] = "CREATE TABLE IF NOT EXISTS OAA_Maps (
    id_map INT(4) NOT NULL AUTO_INCREMENT,

    name VARCHAR(127) NOT NULL,
    scale INT(5) NOT NULL,
    equidistance FLOAT(3) NOT NULL,
    grivation FLOAT(3) NOT NULL,
    geographic_coordinates JSON DEFAULT NULL,
    export_boundaries JSON DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    author JSON NOT NULL,
    private BOOLEAN DEFAULT TRUE,
    map_file VARCHAR(127) DEFAULT NULL,
    imp_file VARCHAR(127) DEFAULT NULL,
    pdf_file VARCHAR(127) DEFAULT NULL,
    gif_file VARCHAR(127) DEFAULT NULL,

    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_edit DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (name),
    PRIMARY KEY (id_map))
    ENGINE=InnoDB";


$sql[] = "INSERT INTO oaa_users (id_user, name, surname, phone, email, password, permission_code, events_access, token, last_login, creation_date) VALUES
        (1, 'Tommaso', 'Bocchietti', '3425016560', 'tommaso.bocchietti@gmail.com', 'TestPWS', 5, '[]', NULL, '2023-03-03 10:33:19', '2023-03-01 22:25:40'),
        (2, 'Filippo', 'Moscatelli', NULL, 'filippo.moscatelli03@gmail.com', 'TestPWS_Moscatelli', 5, '[]', NULL, NULL, '2023-03-01 22:25:40'),
        (3, 'Luca', 'Faini', NULL, 'lucafaini93@gmail.com', 'TestPWS_Faini', 5, '[]', NULL, NULL, '2023-03-01 22:25:40')";

foreach ($sql as $value) Query($value);

$conn->close();
returndata(0, "Connection with MySQL database closed");

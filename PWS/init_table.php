<?php
include "setting.php";

// Pagine table
$sql = "CREATE TABLE IF NOT EXISTS PWS_Pages (
    id_page INT(4) AUTO_INCREMENT,
    name VARCHAR(127) NULL,
    url VARCHAR(127),
    type ENUM('Home','Mix','Elenco','Article','Portal','Undefined') DEFAULT 'Undefined',
    
    forum BOOLEAN DEFAULT 0,
    attachment JSON NULL,
    interactions INT(5) DEFAULT 0,

    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id_page))
    ENGINE=InnoDB";

if (!$conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}


// Utenti table
$sql = "CREATE TABLE IF NOT EXISTS PWS_Users (
    id_user INT(4) AUTO_INCREMENT,
    nickname VARCHAR(127),
    email VARCHAR(127),
    password VARCHAR(127),
    
    color VARCHAR(127) DEFAULT '#ffa500',
	font INT(2) DEFAULT 0,
	avatar VARCHAR(127) DEFAULT '/icon.png',
    -- newsletter BOOLEAN DEFAULT 0,

    verified BOOLEAN DEFAULT 0,
    token VARCHAR(127) DEFAULT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE (nickname),
    UNIQUE (email),
    PRIMARY KEY (id_user))
    ENGINE=InnoDB";

if (!$conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}


// PWS_Interactions table 
$sql = "CREATE TABLE IF NOT EXISTS PWS_Interactions (
    id INT(4) AUTO_INCREMENT,
    month INT(2) NOT NULL,
    year INT(4) NOT NULL,

    PRIMARY KEY (id))
    ENGINE=InnoDB";

if (!$conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}


// PWS_Forum table
$sql = "CREATE TABLE IF NOT EXISTS PWS_Forum (
    id_post INT(5) AUTO_INCREMENT,
    id_page INT(4),
    id_user INT(4),
    
    message VARCHAR(127) NOT NULL,
	position INT(2) DEFAULT 0,
    
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- ultima_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id_post),
    FOREIGN KEY (id_page) REFERENCES PWS_Pages(id_page) ON DELETE NO ACTION ON UPDATE CASCADE,
    FOREIGN KEY (id_user) REFERENCES PWS_Users(id_user) ON DELETE NO ACTION ON UPDATE CASCADE)
    ENGINE=InnoDB";

if (!$conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}



$conn->close();
if ($debug) {
    $return_obj->Log[] = "Connection with MySQL database closed";
}


// mysql_query($query) or die(mysql_error());
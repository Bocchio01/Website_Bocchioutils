<?php
include "../setting.php";

$sql = array();

// Pagine table
$sql[] = "CREATE TABLE IF NOT EXISTS PWS_Pages (
    id_page INT(4) NOT NULL AUTO_INCREMENT,
    name VARCHAR(127) DEFAULT NULL,
    -- url VARCHAR(127),
    type ENUM('Index','Mix','Articolo','Portale','Non definito') DEFAULT 'Non definito',
    
    forum BOOLEAN DEFAULT NULL,
    attachment JSON DEFAULT NULL,
    -- interactions JSON DEFAULT '{\"IT\":0,\"EN\":0,\"JP\":0}',

    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id_page))
    ENGINE=InnoDB";


// Utenti table
$sql[] = "CREATE TABLE IF NOT EXISTS PWS_Users (
    id_user INT(4) NOT NULL AUTO_INCREMENT,
    nickname VARCHAR(127) NOT NULL,
    email VARCHAR(127) NOT NULL,
    password VARCHAR(127) NOT NULL,
    
	theme ENUM('dark','light') DEFAULT 'light',
    color VARCHAR(127) DEFAULT '#ffa500',
	font INT(2) DEFAULT 0,
	avatar VARCHAR(127) DEFAULT '/icon.png',
	lang ENUM('IT','EN') DEFAULT NULL,

    verified BOOLEAN DEFAULT 0,
    token VARCHAR(127) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE (nickname),
    UNIQUE (email),
    PRIMARY KEY (id_user))
    ENGINE=InnoDB";


// PWS_Interactions table 
$sql[] = "CREATE TABLE IF NOT EXISTS PWS_Interactions (
    id INT(4) NOT NULL AUTO_INCREMENT,
    id_page INT(4) NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (id_page) REFERENCES PWS_Pages(id_page) ON DELETE CASCADE ON UPDATE CASCADE)
    ENGINE=InnoDB";


// PWS_Forum table
$sql[] = "CREATE TABLE IF NOT EXISTS PWS_Forum (
    id_post INT(5) NOT NULL AUTO_INCREMENT,
    id_page INT(4),
    id_user INT(4), -- DEFAULT 0,
    
    message LONGTEXT NOT NULL,
	refer INT(2) DEFAULT NULL,
    
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- ultima_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id_post),
    FOREIGN KEY (id_page) REFERENCES PWS_Pages(id_page) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_user) REFERENCES PWS_Users(id_user) ON DELETE SET DEFAULT ON UPDATE CASCADE)
    ENGINE=InnoDB";


// PWS_Traduction table
$sql[] = "CREATE TABLE IF NOT EXISTS PWS_Traduction (
    id INT(4) NOT NULL AUTO_INCREMENT,
    id_page INT(4) NOT NULL,
    IT VARCHAR(127) DEFAULT NULL,
    EN VARCHAR(127) DEFAULT NULL,


    PRIMARY KEY (id),
    FOREIGN KEY (id_page) REFERENCES PWS_Pages(id_page) ON DELETE CASCADE ON UPDATE CASCADE)
    ENGINE=InnoDB";


for ($i = 0; $i < count($sql); $i++) {
    Query($sql[$i]);
}

$conn->close();
returndata(0, "Connection with MySQL database closed");

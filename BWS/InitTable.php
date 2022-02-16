<?php
include "../setting.php";

$sql = array();

// Pagine table
$sql[] = "CREATE TABLE IF NOT EXISTS BWS_Pages (
    id_page INT(4) NOT NULL AUTO_INCREMENT,
    name VARCHAR(127) DEFAULT NULL,
    type ENUM('Index', 'Mix', 'Article', 'Portal', 'Undefined') DEFAULT 'Undefined',
    
    forum BOOLEAN DEFAULT NULL,
    attachment JSON DEFAULT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_page))
    ENGINE=InnoDB";


// Utenti table
$sql[] = "CREATE TABLE IF NOT EXISTS BWS_Users (
    id_user INT(4) NOT NULL AUTO_INCREMENT,
    nickname VARCHAR(127) NOT NULL,
    email VARCHAR(127) NOT NULL,
    password VARCHAR(127) NOT NULL,
    
	theme ENUM('dark','light') DEFAULT 'light',
    color VARCHAR(127) DEFAULT '#ff9800',
	font INT(2) DEFAULT 0,
	avatar VARCHAR(127) DEFAULT '/icon.png',
	lang ENUM('it','en') DEFAULT NULL,

    verified BOOLEAN DEFAULT 0,
    token VARCHAR(127) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    tmp VARCHAR(127) DEFAULT NULL,
    
    UNIQUE (nickname),
    UNIQUE (email),
    PRIMARY KEY (id_user))
    ENGINE=InnoDB";


// BWS_Interactions table 
$sql[] = "CREATE TABLE IF NOT EXISTS BWS_Interactions (
    id INT(4) NOT NULL AUTO_INCREMENT,
    id_page INT(4) NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (id_page) REFERENCES BWS_Pages(id_page) ON DELETE CASCADE ON UPDATE CASCADE)
    ENGINE=InnoDB";


// BWS_Forum table
$sql[] = "CREATE TABLE IF NOT EXISTS BWS_Forum (
    id_post INT(5) NOT NULL AUTO_INCREMENT,
    id_page INT(4),
    id_user INT(4), -- DEFAULT 1,
    
    message LONGTEXT NOT NULL,
	refer INT(2) DEFAULT NULL,
    
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modify TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id_post),
    FOREIGN KEY (id_page) REFERENCES BWS_Pages(id_page) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_user) REFERENCES BWS_Users(id_user) ON DELETE SET NULL ON UPDATE CASCADE)
    ENGINE=InnoDB";


// BWS_Traduction table
$sql[] = "CREATE TABLE IF NOT EXISTS BWS_Traduction (
    id INT(4) NOT NULL AUTO_INCREMENT,
    id_page INT(4) NOT NULL,
    en VARCHAR(127) DEFAULT NULL,
    it VARCHAR(127) DEFAULT NULL,


    PRIMARY KEY (id),
    FOREIGN KEY (id_page) REFERENCES BWS_Pages(id_page) ON DELETE CASCADE ON UPDATE CASCADE)
    ENGINE=InnoDB";


// BWS_Stats table
$sql[] = "CREATE TABLE IF NOT EXISTS BWS_Stats (
    id INT(4) NOT NULL AUTO_INCREMENT,
    year YEAR NULL,
    loading INT(5) DEFAULT 0,
    standalone INT(5) DEFAULT 0,
    total_pageview INT(5) DEFAULT 0,

    PRIMARY KEY (id),
    UNIQUE(year))
    ENGINE=InnoDB";


$sql[] = "INSERT INTO BWS_Pages (name) VALUES ('Error page')";
$sql[] = "INSERT INTO BWS_Traduction (id_page, it, en) VALUES (1, '/it/error_page/', '/error_page/')";
$sql[] = "INSERT INTO BWS_Interactions (id_page) VALUES (1)";

$sql[] = "INSERT INTO BWS_Users (nickname, email, password, lang) VALUES ('Anonimus','anonimus@no-reply.it','Null','it')";


foreach ($sql as $value) Query($value);

$conn->close();
returndata(0, "Connection with MySQL database closed");

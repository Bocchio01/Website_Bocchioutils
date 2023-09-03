<?php
include "../_setting.php";

$sql = array();
$sql[] = "DROP TABLE IF EXISTS RET_Flights_Data";
$sql[] = "DROP TABLE IF EXISTS RET_Flights";
$sql[] = "DROP TABLE IF EXISTS RET_Rockets";

$sql[] = "CREATE TABLE IF NOT EXISTS RET_Rockets (
    id INT(4) NOT NULL AUTO_INCREMENT,
    name VARCHAR(127) NOT NULL,
    diameter INT(5) NOT NULL,
    length INT(5) NOT NULL,
    mass INT(5) NOT NULL,

    UNIQUE(name),
    PRIMARY KEY (id))
    ENGINE=InnoDB";

$sql[] = "CREATE TABLE IF NOT EXISTS RET_Flights (
    id INT(4) NOT NULL AUTO_INCREMENT,
    id_Rocket INT(4) DEFAULT NULL,
    name VARCHAR(127) NOT NULL,
    date TIMESTAMP NOT NULL,
    motor_name VARCHAR(127) NOT NULL,
    motor_mass INT(5) NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (id_Rocket) REFERENCES RET_Rockets(id) ON DELETE SET NULL ON UPDATE CASCADE)
    ENGINE=InnoDB";

$sql[] = "CREATE TABLE IF NOT EXISTS RET_Flights_Data (
    id INT(4) NOT NULL AUTO_INCREMENT,
    id_Flight INT(4) DEFAULT NULL,
    Time FLOAT(8, 3) DEFAULT NULL,
    Temperature FLOAT(8, 2) DEFAULT NULL,
    Pressure INT(6) DEFAULT NULL,
    Altitude FLOAT(8, 2) DEFAULT NULL,
    AccX INT(6) DEFAULT NULL,
    AccY INT(6) DEFAULT NULL,
    AccZ INT(6) DEFAULT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (id_Flight) REFERENCES RET_Flights(id) ON DELETE SET NULL ON UPDATE CASCADE)
    ENGINE=InnoDB";

$sql[] = "INSERT INTO RET_Rockets (name, diameter, length, mass) VALUES
    ('Rocket A', 32, 430, 105),
    ('Rocket V', 34, 657, 224)";

$sql[] = "INSERT INTO RET_Flights (id_Rocket, name, date, motor_name, motor_mass) VALUES
    (1, 'Festa di laurea', '2020-07-21 18:30:00', 'TSP D20-8', 150-105),
    (2, 'Festa di laurea', '2020-07-21 19:00:00', 'TSP F20-8', 316-224),
    (2, 'Festa di laurea', '2023-07-21 21:00:00', 'TSP F20-8', 316-224)";

foreach ($sql as $value) Query($value);

$conn->close();
returndata(0, "Connection with MySQL database closed");

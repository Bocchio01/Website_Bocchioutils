<?php

include_once "functions.php";

// Setting
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

// Variables
$return_obj = new stdClass();
$return_obj->Data = new stdClass();
$return_obj->Status = -1;
$return_obj->Log = array();

$lang = array('IT', 'EN', 'JP');
$type_page = array('Index', 'Mix', 'Articolo', 'Portale', 'Non definito');

// MySQL variables
$nomehost = "localhost";
$database = "my_bocchioutils";
$nomeuser = "root";
$password = "";

// Email variables
$subject = "Bocchio's WebSite";
$headers = "From: no-reply@bocchio.it\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Starting connection to MySQL database
$conn = new mysqli($nomehost, $nomeuser, $password, $database);

if ($conn->connect_error) {
    die(returndata(1, $conn->connect_error));
};

$return_obj->Log[] = "Connection with MySQL database opened";

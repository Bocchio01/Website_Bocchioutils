<?php

include_once "_env.php";
include_once "_functions.php";

// Setting
if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $http_origin = $_SERVER['HTTP_ORIGIN'];
} else {
    $http_origin = "http://localhost:3000";
}

if ($http_origin == HOST_URL || $http_origin == "https://www.bocchio.dev" || $http_origin == "https://bocchio.netlify.app") {
    header("Access-Control-Allow-Origin: $http_origin");
}

header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

// Variables
$return_obj = new stdClass();
$return_obj->Data = new stdClass();
$return_obj->Status = -1;
$return_obj->Log = array();

$lang = array('it', 'en');
$type_page = array('Index', 'Mix', 'Article', 'Portal', 'Undefined');

// MySQL variables
$nomehost = "localhost";
$nomeuser = "root";
$password = "";
$database = "my_bocchioutils";

// Email variables
$subject = "Bocchio's WebSite";
$headers = "From: no-reply@bocchio.dev\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Starting connection to MySQL database
$conn = new mysqli($nomehost, $nomeuser, $password, $database);

if ($conn->connect_error) die(returndata(1, $conn->connect_error));

$return_obj->Log[] = "Connection with MySQL database opened";

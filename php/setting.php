<?php
// ["Log", "MySQL_err", "DataReceived_err", "EmailSender_err", "Result"];

// Functions
function returndata($return_obj) {
    echo (json_encode($return_obj));
}

function isData($value, $return_obj) {
    for ($i=0; $i < count($value); $i++) { 
        if (empty($_POST[$value[$i]])) {
            $return_obj->DataReceived_err[] = "Data not received -> " . $value[$i];
            die(returndata($return_obj));
        }
    }
}

// Setting
$debug = true;
$return_obj = new stdClass();
$return_obj->Result = new stdClass();

header('Access-Control-Allow-Origin: *');

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
$message = "<html><body style='width:80%;margin:0px auto'>";


// Starting connection to MySQL database
$conn = new mysqli($nomehost, $nomeuser, $password, $database);

if ($conn->connect_error) {
    $return_obj->MySQL_err[] = $conn->connect_error;
    die(print_r(json_encode($return_obj)));
};

if ($debug) $return_obj->Log[] = "Connection with MySQL database opened";

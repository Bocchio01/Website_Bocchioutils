<?php

// Functions
function returndata(stdClass $return_obj, int $code = 0, string $log = null)
{
    $return_obj->Log[] = $log;
    $return_obj->Status = $code;
    echo json_encode($return_obj);
}

function isData(array $value, stdClass $return_obj)
{
    for ($i = 0; $i < count($value); $i++) {
        if (!isset($_POST[$value[$i]])) {
            die(returndata($return_obj, 1, "Data not received -> " . $value[$i]));
        }
    }
}

function Query($conn, $sql, $return_obj)
{
    if (!$result = $conn->query($sql)) {
        die(returndata($return_obj, 1, $conn->error));
    } else {
        return $result;
    }
}

function GetAllData($conn, $table, $return_obj)
{
    $result = Query($conn, "SELECT * FROM $table", $return_obj);
    $return_obj->Data = array();
    if ($result->num_rows) while ($row = $result->fetch_array(MYSQLI_ASSOC)) $return_obj->Data[] = $row;
    else $return_obj->Log[] = "The table selected is empty";
}


// Setting
$debug = true;
$return_obj = new stdClass();
$return_obj->Data = new stdClass();
$return_obj->Status = -1;
$return_obj->Log = array();

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

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
    die(returndata($return_obj, 1, $conn->connect_error));
};

$return_obj->Log[] = "Connection with MySQL database opened";

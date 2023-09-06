<?php

include_once "../_setting.php";

function getPost()
{
    if (!empty($_POST)) {
        // when using application/x-www-form-urlencoded or multipart/form-data as the HTTP Content-Type in the request
        // NOTE: if this is the case and $_POST is empty, check the variables_order in php.ini! - it must contain the letter P
        return $_POST;
    }

    // when using application/json as the HTTP Content-Type in the request
    $post = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() == JSON_ERROR_NONE) {
        return $post;
    }

    return [];
}

$_POST = getPost();

// print_r($_POST);

// if (empty($_SERVER['HTTP_ORIGIN'])) {
//     header('Content-Type: text/html; charset=utf-8');
// } else {
if (isset($_POST['data']) && is_string($_POST['data'])) $RCV = json_decode($_POST['data']);
else $RCV = (object) $_POST['data'];
// }

$return_obj->Data->RCV = $RCV;
$RCV = $conn->real_escape_string($RCV);

include_once "userAction.php";
include_once "mapAction.php";


// include_once "../_lib/php-api-nz-mega-object-master/src/";

// $mega = new MEGA();
// $mega->user_login_session($email, $password);


$conn->close();
returndata(0, "Connection with MySQL database closed");

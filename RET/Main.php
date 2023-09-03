<?php

include_once "../_setting.php";
include_once "./_PLM_functions.php";

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

// if (empty($_SERVER['HTTP_ORIGIN'])) {
//     header('Content-Type: text/html; charset=utf-8');
// } else {
if (isset($_POST['data']) && is_string($_POST['data'])) $RCV = json_decode($_POST['data']);
else $RCV = (object) $_POST['data'];
// }

$return_obj->Data->RCV = $RCV;


if (!empty($_POST)) {
    switch ($_POST["action"]) {
        case 'add':
            include_once "add.php";
            break;

        case 'get':
            include_once "get.php";
            break;

        case 'update':
            include_once "update.php";
            break;

        case 'delete':
            include_once "delete.php";
            break;
    }
}

$conn->close();
returndata(0, "Connection with MySQL database closed");

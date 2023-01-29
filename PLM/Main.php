<?php

include_once "../_setting.php";
include_once "./_functions.php";


// if (empty($_SERVER['HTTP_ORIGIN'])) {
//     header('Content-Type: text/html; charset=utf-8');
// } else {
if (isset($_POST['data'])) $RCV = json_decode($_POST['data']);
else $RCV = (object) $_POST;
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

<?php
include "setting.php";

isData(["color", "font", "nickname", "avatar"], $return_obj);

$color = $_POST["color"];
$font = $_POST["font"];
$nickname = $_POST["nickname"];
$avatar = $_POST["avatar"];
if ($debug) {
    $return_obj->Log[] = "Nickname ricevuto: $nickname";
    $return_obj->Log[] = "Colore ricevuto: $color";
    $return_obj->Log[] = "Font ricevuto: $font";
    $return_obj->Log[] = "Avatar ricevuto: $avatar";
}


$sql = "UPDATE PWS_Users SET colore='$color', font='$font', avatar='$avatar' WHERE nickname='$nickname'";

if (!$result = $conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

$conn->close();
if ($debug) {
    $return_obj->Log[] = "Connection with MySQL database closed";
}

$return_obj->Result->Status = 1;
returndata($return_obj);

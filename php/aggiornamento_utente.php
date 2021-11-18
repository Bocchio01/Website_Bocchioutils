<?php
include "setting.php";

isData(["color", "font", "nickname"], $return_obj);

$color = $_POST["color"];
$font = $_POST["font"];
$nickname = $_POST["nickname"];
if ($debug) {
    $return_obj->Log[] = "Nickname ricevuto: $nickname";
    $return_obj->Log[] = "Colore ricevuto: $color";
    $return_obj->Log[] = "Font ricevuto: $font";
}


$sql = "UPDATE Utenti SET colore='$color', font='$font' WHERE nickname='$nickname'";

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

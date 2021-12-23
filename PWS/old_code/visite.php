<?php
include "setting.php";

isData(["url"], $return_obj);

$url = $_POST["url"];
if ($debug) $return_obj->Log[] = "URL ricevuto: $url";


if (strlen($url) > 127) {
    $return_obj->DataReceived_err[] = "L'URL è troppo lungo: " . strlen($url) . " caratteri";
    die(returndata($return_obj));
}


$sql = "SELECT num_visite FROM PWS_Interactions WHERE url_page='$url' limit 1";

if (!$result = $conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

if ($result->num_rows) {
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $util = ++$row["num_visite"];
    $sql = "UPDATE Visite_sito SET num_visite='$util' WHERE url_page='$url'";

    if (!$conn->query($sql)) {
        $return_obj->MySQL_err[] = $conn->error;
        die(returndata($return_obj));
    }
    if ($debug) $return_obj->Log[] = "Aggiorno il campo";

} else {
    $sql = "INSERT INTO Visite_sito (url_page) VALUES ('$url')";
    if (!$conn->query($sql)) {
        $return_obj->MySQL_err[] = $conn->error;
        die(returndata($return_obj));
    }
    if ($debug) $return_obj->Log[] = "Creo il campo";
}

$conn->close();
if ($debug) {
    $return_obj->Log[] = "Connection with MySQL database closed";
}

$return_obj->Result->Status = 1;
returndata($return_obj);

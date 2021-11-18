<?php

include "setting.php";

isData(["table"], $return_obj);
$table = $_POST["table"];

if ($debug) $return_obj->Log[] = "Tabella selezionata: $table";

$sql = "SELECT * FROM $table";


if (!$result = $conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

if ($result->num_rows) {
    while ($row = $result -> fetch_array(MYSQLI_ASSOC)) {
        $return_obj->Result->Data[] = $row;
    }
} else {
    if ($debug) $return_obj->Log[] = "Nella tabella selezionata non ci sono dati";
}

$return_obj->Result->Status = 1;
returndata($return_obj);

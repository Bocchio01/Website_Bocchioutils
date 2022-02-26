<?php

include "../php/_setting.php";

isData(["table"]);
$table = $_POST["table"];

if (isset($_POST['id'])) $id = $_POST["id"];
else $id = 0;

$return_obj->Log[] = "Tabella selezionata: $table";
$return_obj->Log[] = "Id selezionato: $id";

if ($id) {
    $sql = "SELECT * FROM $table WHERE id_torneo ='$id'";
} else {
    $sql = "SELECT * FROM $table";
}


if (!$result = $conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

if ($result->num_rows) {
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $return_obj->Result->Data[] = $row;
    }
} else {
    $return_obj->Log[] = "Nella tabella selezionata non ci sono dati";
}

$return_obj->Result->Status = 1;
returndata($return_obj);

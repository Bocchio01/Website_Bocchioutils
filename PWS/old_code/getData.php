<?php

include "setting.php";

isData(["table"], $return_obj);
$table = $_POST["table"];

$result = Query($conn, "SELECT * FROM $table", $return_obj);

if ($result->num_rows) while ($row = $result->fetch_array(MYSQLI_ASSOC)) $return_obj->Result->Data[] = $row;
else $return_obj->Log[] = "Nella tabella selezionata non ci sono dati";

$return_obj->Result->Status = 0;
returndata($return_obj);

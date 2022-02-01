<?php

include "../php/setting.php";

isData(["nickname", "nome_torneo"]);
$nickname = $_POST["nickname"];
$nome_torneo = $_POST["nome_torneo"];

$return_obj->Log[] = "Nickname ricevuto: $nickname";
$return_obj->Log[] = "Nome torneo ricevuto: $nome_torneo";

$sql = "INSERT INTO CalcioBalilla_Tornei (Creatore, nome_torneo) VALUES ('$nickname','$nome_torneo')";
if (!$conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

$return_obj->Result->Status = 1;
returndata($return_obj);

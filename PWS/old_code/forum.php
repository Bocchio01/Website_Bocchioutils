<?php
include "setting.php";

isData(["nickname", "msg", "position", "article_name"], $return_obj);

$nickname = $_POST["nickname"];
$msg = $_POST["msg"];
$position = $_POST["position"];
$article_name = $_POST["article_name"];

if ($debug) {
    $return_obj->Log[] = "Nickname ricevuto: $nickname";
    $return_obj->Log[] = "Messaggio ricevuto: $msg";
    $return_obj->Log[] = "Posizione ricevuta: $position";
    $return_obj->Log[] = "Nome_articolo ricevuto: $article_name";
}


$sql = "INSERT INTO PWS_Forum (nome_pagina, nome_autore, msg) VALUES ('$article_name','$nickname','$msg')";
if (!$conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

$conn->close();
if ($debug) {
    $return_obj->Log[] = "Connection with MySQL database closed";
}


$return_obj->Result->Status = 1;
returndata($return_obj);

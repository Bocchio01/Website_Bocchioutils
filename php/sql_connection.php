<?php

$conn = new mysqli($nomehost, $nomeuser, $password, $database);

if ($conn->connect_error) {
    $return_obj[] = "Connessione fallita: $conn->connect_error.";
    print_r(json_encode($return_obj));
};

if ($debug) $return_obj[] = "Connessione al database effettuata correttamante";

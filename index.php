<?php

$to_key = $_GET["key"];

if(!$to_key) {
    die("Errore, invalid URL");
}

switch ($to_key) {
    case 'util':
        header('location: PWS_Interactions.html', 200);
        break;

    default:
        header('location: https://bocchionuxt.netlify.app/', 200);
        break;
}

exit;

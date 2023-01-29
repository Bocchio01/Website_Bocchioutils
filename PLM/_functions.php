<?php

require_once './_table.php';
require_once './_graphs.php';


function timeLength(int $minutes)
{
    $m = $minutes % 60;
    $h = (($minutes - $m) / 60);
    return $h . ":" . substr("0" . $m, -2);
}


function checkAuthorization()
{
    $token = '';
    $PLM_token = '';
    if (!empty($_COOKIE['token'])) $token = $_COOKIE['token'];
    if (!empty($_COOKIE['PLM_token'])) $PLM_token = $_COOKIE['PLM_token'];

    if (empty($token) && empty($PLM_token)) {
        die(returndata(1, 'Not authorized.'));
    }

    $result = Query("SELECT P.*, U.token as BWS_token
    FROM PLM_Professor as P
    LEFT JOIN BWS_Users as U ON U.id_user = P.id_BWS
    WHERE U.token = '$token' OR P.token = '$PLM_token'");

    if ($result->num_rows == 1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        Query("UPDATE PLM_Professor SET last_login = NOW() WHERE id = '$row[id]'");

        if ($row['approved'] == 1) {

            setcookie('token', $row['BWS_token'], time() + 3600 * 24 * 30, '/');
            setcookie('PLM_token', $row['token'], time() + 3600 * 24 * 30, '/');

            return $row['id'];
        } else {
            die(returndata(1, 'Professore non approvato.'));
        }
    } else {
        die(returndata(1, 'Professore non riconosciuto.'));
    }
}


function calculatePrice($minutes, $price_per_hour)
{
    $nSteps = 2;
    $price_per_step = $price_per_hour / $nSteps;

    $price = ((int) (($minutes + 60 / $nSteps / 2) / 60 * $nSteps)) * $price_per_step;

    return $price;
}

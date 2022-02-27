<?php

include "_setting.php";
$login = 0;

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $result = Query("SELECT id_user, token FROM BWS_Users WHERE (email, password) = ('$email','$password')");
    if ($result->num_rows) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if ($row['id_user'] == 2) {
            $login = 1;
            Cookie($row['token']);
        }
    }
}

if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    $id_user = Query("SELECT id_user FROM BWS_Users WHERE token = '$token'")->fetch_array(MYSQLI_ASSOC)['id_user'];
    if ($id_user == 2) {
        $login = 1;
        Cookie($token);
    }
}

if (isset($_POST['api_key'])) {
    $key = $_POST['api_key'];
    if ($key == API_KEY) $login = 1;
}

if (isset($_GET['api_key'])) {
    $key = $_GET['api_key'];
    if ($key == API_KEY) $login = 1;
}

if (!$login) {
    ClearCookie();
    error_log(date(DATE_RSS) . " -> Login non riuscito\n" . print_r($_SERVER, true) . "\n\n---------------------\n\n", 3, '/log.txt');
}

<?php

include "../../setting.php";
header('Content-Type: text/html; charset=utf-8');

$action = -1;

if (isset($_GET['action'])) {
    $error = -1;
    $action = $_GET['action'];
    switch ($action) {

        case 'VerifyEmail':
            if (isset($_GET["tmp"])) {
                $tmp = $_GET["tmp"];

                if (Query("SELECT verified FROM BWS_Users WHERE (tmp, verified) = ('$tmp', 0)")->num_rows) {
                    Query("UPDATE BWS_Users SET verified=1, tmp=NULL WHERE tmp='$tmp'");
                    $error = 0;
                } else $error = 1;
            } else $error = 2;
            break;


        case 'ForgotPassword':
            if (isset($_POST["submit"])) {
                $email = strtolower($_POST["email"]);

                if ($id_user = Query("SELECT id_user FROM BWS_Users WHERE email='$email'")->fetch_array(MYSQLI_ASSOC)['id_user']) {
                    $tmp = CreateToken(5);
                    Query("UPDATE BWS_Users SET tmp='$tmp' WHERE id_user='$id_user'");

                    $message = render('./template/ForgotPassword.php', array('tmp' => $tmp, 'email' => $email));

                    if (mail($email, $subject, $message, $headers)) $error = 0;
                    else $error = 1;
                } else $error = 2;
            }
            break;

        case 'ModifyPassword':
            if (isset($_POST["submit"])) {
                $email = strtolower($_POST["email"]);
                $old_password = md5($_POST["old_password"]);
                $new_password = md5($_POST["new_password"]);

                if ($id_user = Query("SELECT id_user FROM BWS_Users WHERE (email, password)=('$email', '$old_password')")->fetch_array(MYSQLI_ASSOC)['id_user']) {
                    $token = CreateToken();

                    Query("UPDATE BWS_Users SET password='$new_password', token='$token' WHERE id_user='$id_user'");
                    $error = 0;
                } else $error = 1;
            }
            break;

        case 'ModifyPasswordEmail':
            if (isset($_POST["submit"])) {
                $email = strtolower($_POST["email"]);
                $tmp = $_POST["tmp"];
                if (strlen($_POST["new_password"]) >= 5) {

                    $new_password = md5($_POST["new_password"]);

                    if ($id_user = Query("SELECT id_user FROM BWS_Users WHERE (email, tmp)=('$email', '$tmp')")->fetch_array(MYSQLI_ASSOC)['id_user']) {
                        $token = CreateToken();

                        Query("UPDATE BWS_Users SET password='$new_password', tmp=NULL, token='$token' WHERE id_user='$id_user'");
                        $error = 0;
                    } else $error = 1;
                } else $error = 2;
            }
            break;


        default:
            $action = -1;
            break;
    }
}

$conn->close();
unset($_POST);

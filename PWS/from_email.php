<?php

include "setting.php";

switch ($_GET['action']) {

    case 'VerifyEmail':
        if (isset($_GET["token"])) {
            $token = $_GET["token"];

            if (Query($conn, "SELECT verified FROM PWS_Users WHERE (token, verified) = ('$token', 0)", $return_obj)->num_rows) {
                Query($conn, "UPDATE PWS_Users SET verified=1 WHERE token='$token'", $return_obj);
                echo ("Succesfully verified. Redirect to main Bocchio's WebSite...");
            } else {
                echo ("You have already verified your email succesfully. Redirect to main Bocchio's WebSite...");
            }
        } else {
            echo "Token not valid";
        }
        $conn->close();
        header("refresh:2;url=https://bocchionuxt.netlify.app/");
        break;

    case 'ForgotPassword':
        header('Content-Type: text/html; charset=utf-8');
        if (isset($_GET["token"])) {
            $token = $_GET["token"];

            if ($id_user = Query($conn, "SELECT id_user FROM PWS_Users WHERE token='$token' limit 1", $return_obj)->fetch_array(MYSQLI_ASSOC)['id_user']) {

                echo "<form action='' method='post'>
<input type='text' name='password' placeholder='Nuova password' minlength='5' >
<input type='submit' name='submit' value='Invia'>
</form>";

                if (isset($_POST["submit"])) {

                    $password = md5($_POST["password"]);

                    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $token = '';
                    for ($i = 0; $i < 15; $i++) $token .= $characters[rand(0, strlen($characters) - 1)];

                    Query($conn, "UPDATE PWS_Users SET password='$password', token='$token' WHERE id_user='$id_user'", $return_obj);

                    echo "<br>Password modificata con successo!<br>Redirect to main site..";
                    header("refresh:4;url=https://bocchionuxt.netlify.app/");
                }


                // echo ("Here your password: $password");
            } else {
                echo ("User not valid!");
                header("refresh:2;url=https://bocchionuxt.netlify.app/");
            }
        } else {
            echo "Token not valid";
        }
        $conn->close();
        break;


    default:
        echo "Token not valid";
        header("refresh:2;url=https://bocchionuxt.netlify.app/");
        break;
}

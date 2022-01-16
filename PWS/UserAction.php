<?php
switch ($_POST["action"]) {

    case 'UserUpdate':
        $token = $RCV->token;
        $nickname = $RCV->nickname;
        $dark = (int) $RCV->preferences->dark;
        $color = $RCV->preferences->color;
        $font = (int) $RCV->preferences->font;
        $avatar = $RCV->preferences->avatar;

        Query($conn, "UPDATE PWS_Users SET dark=$dark, color='$color', font=$font, avatar='$avatar' WHERE token='$token'", $return_obj);
        if (strlen($nickname) > 0) Query($conn, "UPDATE PWS_Users SET nickname='$nickname' WHERE token='$token'", $return_obj);
        else die(returndata($return_obj, 1, "Il nickname non può essere nullo!"));

        break;

    case 'UserSignup':
        $nickname = $RCV->nickname;
        $email = strtolower($RCV->email);
        $password = $RCV->password;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die(returndata($return_obj, 1, "Formato dell'email non valido."));
        if (strlen($password) < 5) die(returndata($return_obj, 1, "La password deve avere almeno 5 caratteri."));

        $password = md5($password);

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        for ($i = 0; $i < 15; $i++) $token .= $characters[rand(0, strlen($characters) - 1)];

        Query($conn, "INSERT INTO PWS_Users (nickname, email, password, token) VALUES ('$nickname','$email','$password','$token')", $return_obj);

        $message .= "<h1 style='text-align:center;font-size:25px;margin-bottom:20px'>Benvenuto $nickname!<h1><p style='text-align:left;font-size:15px'>Grazie per aver deciso di iscriverti al mio sito e quindi di supportarmi in quello che faccio :).<br>
Con questo messaggio voglio semplicemente verificare che l'email inserita nel modulo del sito sia corretta.<br><br>
Clicca su <a href='https://bocchioutils.altervista.org/PWS/verifica_email.php?token=$token'>questo link</a> per confermarla.<br><br>
Se pensi che questa email abbia avuto un destinatario erroneo, ignorala.<br><br>
Il WebMaster di Bocchio's WebSite,<br>
Tommaso<br></p>";

        if (mail($email, $subject, $message .= "</body> </html>", $headers)) $return_obj->Log[] = "Email inviata correttamente a: $email";
        else die(returndata($return_obj, 1, "C'è stato un problema durante l'invio dell'email a: $email"));

        break;


    case 'UserLogin':
        $email = strtolower($RCV->email);
        $password = md5($RCV->password);
        $token = $RCV->token;


        if ($password && $email) $result = Query($conn, "SELECT * FROM PWS_Users WHERE email = '$email'", $return_obj);
        else $result = Query($conn, "SELECT * FROM PWS_Users WHERE token = '$token'", $return_obj);


        if ($result->num_rows) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            if ($row['password'] == $password || $token) {
                if ($row['verified'] == 1) {
                    $return_obj->Data->token = $row['token'];
                    $return_obj->Data->nickname = $row['nickname'];
                    $return_obj->Data->email = $row['email'];
                    $return_obj->Data->password = '';

                    $return_obj->Data->preferences = new stdClass;
                    $return_obj->Data->preferences->dark = (bool) $row['dark'];
                    $return_obj->Data->preferences->color = $row['color'];
                    $return_obj->Data->preferences->font = (int) $row['font'];
                    $return_obj->Data->preferences->avatar = $row['avatar'];

                    Query($conn, "UPDATE PWS_Users SET last_login=NOW() WHERE token = '" . $row['token'] . "'", $return_obj);
                } else {
                    die(returndata($return_obj, 1, "Prima di accedere devi verificare l'email. Controlla la tua casella di posta elettronica."));
                }
            } else {
                die(returndata($return_obj, 1, "La password è sbagliata!"));
            }
        } else {
            die(returndata($return_obj, 1, "L'utente non esite. Controlla l'email inserita."));
        }
        break;
}

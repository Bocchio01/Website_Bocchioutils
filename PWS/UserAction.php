<?php
switch ($_POST["action"]) {

    case 'UserUpdate':
        $id_user = $RCV->id;
        $nickname = $RCV->nickname;
        $theme = $RCV->preferences->theme;
        $color = $RCV->preferences->color;
        $font = (int) $RCV->preferences->font;
        $avatar = $RCV->preferences->avatar;
        // $lang = $RCV->preferences->lang;

        Query("UPDATE PWS_Users SET theme='$theme', color='$color', font=$font, avatar='$avatar' WHERE id_user='$id_user'");
        if (strlen($nickname) > 0) Query("UPDATE PWS_Users SET nickname='$nickname' WHERE id_user='$id_user'");
        else die(returndata(1, "Il nickname non può essere nullo!"));

        break;

    case 'UserSignup':
        $nickname = $RCV->nickname;
        $email = strtolower($RCV->email);
        $password = $RCV->password;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die(returndata(1, "Formato dell'email non valido."));
        if (strlen($password) < 5) die(returndata(1, "La password deve avere almeno 5 caratteri."));

        $password = md5($password);

        $token = CreateToken();

        Query("INSERT INTO PWS_Users (nickname, email, password, token) VALUES ('$nickname','$email','$password','$token')");

        $message = render('./template/UserSignup.php', array('nickname' => $nickname, 'token' => $token));

        if (mail($email, $subject, $message, $headers)) $return_obj->Log[] = "Email inviata correttamente a: $email";
        else die(returndata(1, "C'è stato un problema durante l'invio dell'email a: $email"));

        break;


    case 'UserLogin':
        $token = false;
        if (!empty($_COOKIE['token'])) $token = $_COOKIE['token'];
        $email = strtolower($RCV->email);
        $password = md5($RCV->password);
        $autologin = $RCV->autologin;

        if ($password && $email) $result = Query("SELECT * FROM PWS_Users WHERE email = '$email'");
        else $result = Query("SELECT * FROM PWS_Users WHERE token = '$token'");

        if ($result->num_rows) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            if ($row['password'] == $password || $token) {
                if ($row['verified'] == 1) {
                    $return_obj->Data->id = $row['id_user'];
                    $return_obj->Data->nickname = $row['nickname'];
                    $return_obj->Data->email = $row['email'];
                    $return_obj->Data->password = $RCV->password;
                    $return_obj->Data->autologin = $autologin;

                    $return_obj->Data->preferences = new stdClass();
                    $return_obj->Data->preferences->theme = $row['theme'];
                    $return_obj->Data->preferences->color = $row['color'];
                    $return_obj->Data->preferences->font = (int) $row['font'];
                    $return_obj->Data->preferences->avatar = $row['avatar'];
                    $return_obj->Data->preferences->lang = $row['lang'];

                    Query("UPDATE PWS_Users SET last_login=NOW() WHERE id_user = '$row[id_user]'");

                    if ($token || $autologin) setcookie("token", "$row[token]", time() + 60 * 60 * 24 * 30, "/", "localhost", false, false);
                    else setcookie("token");
                } else die(returndata(1, "Prima di accedere devi verificare l'email. Controlla la tua casella di posta elettronica."));
            } else die(returndata(1, "La password è sbagliata!"));
        } else die(returndata(1, "L'utente non esite. Controlla l'email inserita."));

        // token_test=KSu3MWgYqNDCs8K; token=KSu3MWgYqNDCs8K; token=

        break;

    case 'ForgotPassword':
        $email = strtolower($RCV->email);

        $token = Query("SELECT token FROM PWS_Users WHERE email='$email' limit 1")->fetch_array(MYSQLI_ASSOC)['token'];

        if ($token) {

            $message = render('./template/ForgotPassword.php', array('token' => $token, 'email' => $email));

            if (mail($email, $subject, $message .= "</body> </html>", $headers)) $return_obj->Data = "L'email con le istruzioni per il recupero credenziali è appena stata inviata a: $email";
            else die(returndata(1, "C'è stato un problema durante l'invio dell'email a $email con le istruzioni per il recupero credenziali... Riprova"));
        } else die(returndata(1, "Non esiste alcun utente associato all'email: $email"));

        break;
}

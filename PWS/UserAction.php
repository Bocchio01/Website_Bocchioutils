<?php
switch ($_POST["action"]) {

    case 'UserUpdate':
        $nickname = $RCV->nickname;
        $color = $RCV->preferences->color;
        $font = $RCV->preferences->font;
        $avatar = $RCV->preferences->avatar;

        Query($conn, "UPDATE PWS_Users SET color='$color', font='$font', avatar='$avatar' WHERE nickname='$nickname'", $return_obj);
        break;

    case 'UserSignup':
        $nickname = $RCV->nickname;
        $email = strtolower($RCV->email);
        $password = $RCV->password;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die(returndata($return_obj, 1, "Invalid email format"));
        }
        if (strlen($password) < 5) {
            die(returndata($return_obj, 1, "Password must be longer than 5 characters"));
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        for ($i = 0; $i < 5; $i++) $token .= $characters[rand(0, strlen($characters) - 1)];

        Query($conn, "INSERT INTO PWS_Users (nickname, email, password, token) VALUES ('$nickname','$email','$password','$token')", $return_obj);

        $message .= "<h1 style='text-align:center;font-size:25px;margin-bottom:20px'>Benvenuto $nickname!<h1><p style='text-align:left;font-size:15px'>Grazie per aver deciso di iscriverti al mio sito e quindi di supportarmi in quello che faccio :).<br>
Con questo messaggio voglio semplicemente verificare che l'email inserita nel modulo del sito sia corretta.<br><br>
Clicca su <a href='https://bocchioutils.altervista.org/php/verifica_email.php?token=$token'>questo link</a> per confermarla.<br><br>
Se pensi che questa email abbia avuto un destinatario erroneo, ignorala.<br><br>
Il WebMaster di Bocchio's WebSite,<br>
Tommaso<br></p>";

        if (mail($email, $subject, $message .= "</body> </html>", $headers)) {
            $return_obj->Log[] = "Email successfully sent to $email";
        } else {
            die(returndata($return_obj, 1, "Email sending to $email failed"));
        }
        break;


    case 'UserLogin':
        $email = strtolower($RCV->email);
        $password = $RCV->password;
        $token = $RCV->token;

        if ($password && $email) {
            $result = Query($conn, "SELECT * FROM PWS_Users WHERE email = '$email'", $return_obj);
        } else {
            $result = Query($conn, "SELECT * FROM PWS_Users WHERE token = '$token'", $return_obj);
        }

        if ($result->num_rows) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            if ($row['password'] == $password || $token) {
                if ($row['verified'] == 1) {
                    $return_obj->Data = $row;
                } else {
                    die(returndata($return_obj, 1, "Prima di accedere devi verificare l'email. Controlla la tua casella di posta elettronica."));
                }
            } else {
                die(returndata($return_obj, 1, "La password è sbagliata!"));
            }
        } else {
            die(returndata($return_obj, 1, "L'utente non esite. Controlla il nickname inserito."));
        }
        break;
}

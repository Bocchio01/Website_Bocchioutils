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

        $message .= "<h1 style='text-align:center;font-size:25px;margin-bottom:20px'>Benvenuto $nickname!<h1><p style='text-align:left;font-size:15px'>Grazie per aver deciso di iscriverti al mio sito e quindi di supportarmi in quello che faccio :).<br>Con questo messaggio voglio semplicemente verificare che l'email inserita nel modulo del sito sia corretta.<br><br>Clicca su <a href='https://bocchioutils.altervista.org/PWS/from_email.php?action=VerifyEmailtoken=$token'>questo link</a> per confermarla.<br><br>Se pensi che questa email abbia avuto un destinatario erroneo, ignorala.<br><br>Il WebMaster di Bocchio's WebSite,<br>Tommaso<br></p>";

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
                    $return_obj->Data->id = $row['id_user'];
                    $return_obj->Data->token = $row['token'];
                    $return_obj->Data->nickname = $row['nickname'];
                    $return_obj->Data->email = $row['email'];
                    $return_obj->Data->password = $RCV->password;

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

    case 'ForgotPassword':
        $email = strtolower($RCV->email);

        $token = Query($conn, "SELECT token FROM PWS_Users WHERE email='$email' limit 1", $return_obj)->fetch_array(MYSQLI_ASSOC)['token'];
        echo $token;
        if ($token) {
            $message .= "<h1 style='text-align:center;font-size:25px;margin-bottom:20px'>Recupero/cambio credenziali!<h1>
            <p style='text-align:left;font-size:15px'>Ciao, se hai ricevuto questa email vuol dire che qualcuno ha
                richiesto un 'recupero password' per l'account creato sul sito 'Bocchio's WebSite' collegato a questa
                email.<br>Se non sei stato tu a richiedere questo servizio, sarebbe oppurtuno contattassi <a
                    href='mailto:tommaso.bocchietti@gmail.com?subject=Recupero credenziali sito inaspettato&body=Richiesta effettuata per l'
                    account legato all email: $email'>l'amministrazione del sito</a><br><br>In caso contrario
                invece,
                clicca su <a
                    href='https://bocchioutils.altervista.org/PWS/from_email.php?action=ForgotPassword&token=$token'>questo
                    link</a> e potrai generare una nuova password.<br><br>ATTENZIONE!<br>Il link sopra è valevole solo
                una volta.
                Nel caso volessi recuperare/cambiare nuovamente la tua password dovrai ri-chiederla dal sito 'Bocchio's
                WebSite' tramite la schermata di login.<br>Per ragioni di sicurezza, una volta cliccato sul link sopra
                sarai
                disconnesso dal tuo account da ogni dispositivo e dovrai ri-loggarti nel sito.<br><br>Il WebMaster di
                Bocchio's WebSite,<br>Tommaso<br></p>";

            if (mail($email, $subject, $message .= "</body> </html>", $headers)) $return_obj->Data = "L'email con le istruzioni per il recupero credenziali è appena stata inviata a: $email";
            else die(returndata($return_obj, 1, "C'è stato un problema durante l'invio dell'email a $email con le istruzioni per il recupero credenziali... Riprova"));
        } else die(returndata($return_obj, 1, "Non esiste alcun utente associato all'email: $email"));

        break;
}

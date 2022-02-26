<?php
switch ($_POST["action"]) {

    case 'UserUpdate':
        $id_user = $RCV->id;
        $nickname = $RCV->nickname;
        $theme = $RCV->preferences->theme;
        $color = $RCV->preferences->color;
        $font = (int) $RCV->preferences->font;
        $avatar = $RCV->preferences->avatar;
        $lang = $RCV->preferences->lang;
        $newsletter = (int) $RCV->preferences->newsletter;

        Query("UPDATE BWS_Users SET theme='$theme', color='$color', font=$font, avatar='$avatar', lang='$lang', newsletter=$newsletter WHERE id_user='$id_user'");
        if (strlen($nickname) > 0) Query("UPDATE BWS_Users SET nickname='$nickname' WHERE id_user='$id_user'");
        else die(returndata(1, "Nickname can't be null."));

        break;

    case 'UserSignup':
        $nickname = $RCV->nickname;
        $email = strtolower($RCV->email);
        $password = $RCV->password;
        $lang = $RCV->preferences->lang;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die(returndata(1, "Email format not valid."));
        if (strlen($password) < 5) die(returndata(1, "Password must have at least 5 characters."));

        $password = md5($password);

        $token = CreateToken();
        $tmp = CreateToken(5);

        Query("INSERT INTO BWS_Users (nickname, email, password, lang, token, tmp) VALUES ('$nickname','$email','$password','$lang','$token','$tmp')");

        $message = render('./template/' . $lang . '/UserSignup.php', array('nickname' => $nickname, 'tmp' => $tmp));

        if (mail($email, $subject, $message, $headers)) $return_obj->Log[] = "An e-mail has just been sended to: $email";
        else die(returndata(1, "There was a problem while sending e-mail to: $email\r\nCheck the e-mail or try again later."));

        break;


    case 'UserLogin':
        $token = false;
        if (!empty($_COOKIE['token'])) $token = $_COOKIE['token'];
        $email = strtolower($RCV->email);
        $password = md5($RCV->password);
        $autologin = $RCV->autologin;

        if ($password && $email) $result = Query("SELECT * FROM BWS_Users WHERE email = '$email'");
        else $result = Query("SELECT * FROM BWS_Users WHERE token = '$token'");

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
                    $return_obj->Data->preferences->newsletter = (bool) $row['newsletter'];

                    Query("UPDATE BWS_Users SET last_login=NOW() WHERE id_user = '$row[id_user]'");

                    if ($token || $autologin) {
                        setcookie('token', "$row[token]", [
                            'expires' => time() + 60 * 60 * 24 * 30,
                            'path' => "/",
                            'samesite' => 'None',
                            'secure' => 'Secure',
                            'httponly' => false,
                        ]);
                    } else {
                        ClearCookie();
                    }
                } else {
                    ClearCookie();
                    die(returndata(1, "You mut verify your e-mail before logging in. Check your e-mail box."));
                }
            } else {
                ClearCookie();
                die(returndata(1, "The password is uncorrect!"));
            }
        } else {
            ClearCookie();
            die(returndata(1, "The user doesn't exist. Check the e-mail you entered."));
        }
        break;


    case 'UserLogout':
        ClearCookie();

        break;
}

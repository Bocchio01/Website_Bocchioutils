<?php

switch ($_POST["action"]) {
    case 'UserLogin':
        $token = $RCV->token;
        $email = $RCV->email;
        $password = $RCV->password;
        // $password = md5($RCV->password);

        // $token = '';
        // $nickname = 'Tommaso4';
        // $password = '4';

        if ($email && $password) $result = Query("SELECT * FROM OAA_Users WHERE email = '$email'");
        else $result = Query("SELECT * FROM OAA_Users WHERE token = '$token'");

        if ($result->num_rows) {
            $row = $result->fetch_array(MYSQLI_ASSOC);

            if ($row['password'] == $password || $token) {

                $return_obj->Data = $row;
                $return_obj->Data['password'] = null;

                Query("UPDATE OAA_Users SET last_login=NOW() WHERE id_user = '$row[id_user]'");
                setcookie("OAAtoken", $row['token'], time() + (86400 * 30), "/");
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
        setcookie("OAAtoken", '', 0, "/");
        break;

    default:
        # code...
        break;
}

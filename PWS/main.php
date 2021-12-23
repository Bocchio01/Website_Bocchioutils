<?php
include "setting.php";



foreach ($_POST as $key => $value) {
    $return_obj->Log[] = "Field " . htmlspecialchars($key) . ": " . htmlspecialchars($value);
}

switch ($_POST["action"]) {
    case 'GetAllData':
        isData(["table"], $return_obj);
        $table = $_POST["table"];
        
        GetAllData($conn, $table, $return_obj);
        break;

    case 'Agg_setting':
        isData(["color", "font", "nickname", "avatar"], $return_obj);

        $color = $_POST["color"];
        $font = $_POST["font"];
        $nickname = $_POST["nickname"];
        $avatar = $_POST["avatar"];

        Query($conn, "UPDATE PWS_Users SET color='$color', font='$font', avatar='$avatar' WHERE nickname='$nickname'", $return_obj);
        break;

    case 'Sign_up':
        isData(["nickname", "email", "pwd"], $return_obj);

        $nickname = $_POST["nickname"];
        $email = strtolower($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $return_obj->Error = "Invalid email format";
            die(returndata($return_obj));
        }
        $password = $_POST["pwd"];
        if (strlen($password) < 5) {
            $return_obj->Error = "Password must be longer than 5 characters";
            die(returndata($return_obj));
        }
        // $newsletter = ($_POST["newsletter"] == 'on') ? 1 : 0;

        Query($conn, "INSERT INTO PWS_Users (nickname, email, password) VALUES ('$nickname','$email','$password')", $return_obj);

        $message .= "<h1 style='text-align:center;font-size:25px;margin-bottom:20px'>Benvenuto " . $nickname . "!<h1><p style='text-align:left;font-size:15px'>Grazie per aver deciso di iscriverti al mio sito e quindi di supportarmi in quello che faccio :).<br>
Con questo messaggio voglio semplicemente verificare che l'email inserita nel modulo del sito sia corretta.<br><br>
Clicca su <a href='https://bocchioutils.altervista.org/php/verifica_email.php?email=" . $email . "'>questo link</a> per confermarla.<br><br>
Se pensi che questa email abbia avuto un destinatario erroneo, ignorala.<br><br>
Il WebMaster di Bocchio's WebSite,<br>
Tommaso<br></p>";

        if (mail($email, $subject, $message .= "</body> </html>", $headers)) {
            $return_obj->Log[] = "Email successfully sent to $email";
        } else {
            $return_obj->Error[] = "Email sending to $email failed";
            $return_obj->Result->Status = 1;
            die(returndata($return_obj));
        }
        break;

    case 'Login':
        isData(["email", "pwd"], $return_obj);

        $email = strtolower($_POST["email"]);
        $password = $_POST["pwd"];

        $result = Query($conn, "SELECT * FROM PWS_Users WHERE email = '$email'", $return_obj);

        if ($result->num_rows) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            if ($row['password'] == $password) {
                if ($row['verified'] == 1) {

                    $return_obj->Result->Color = $row['color'];
                    $return_obj->Result->Font = $row['font'];
                    $return_obj->Result->Avatar = $row['avatar'];
                    $return_obj->Result->Nickname = $row['nickname'];
                    $return_obj->Result->Email = $row['email'];
                    $return_obj->Result->Password = $row['password'];
                } else {
                    $return_obj->Error = "Prima di accedere devi verificare l'email. Controlla la tua casella di posta elettronica.";
                    $return_obj->Result->Status = 1;
                    die(returndata($return_obj));
                }
            } else {
                $return_obj->Error = "La password è sbagliata!";
                $return_obj->Result->Status = 1;
                die(returndata($return_obj));
            }
        } else {
            $return_obj->Error = "L'utente non esite. Controlla il nickname inserito.";
            $return_obj->Result->Status = 1;
            die(returndata($return_obj));
        }
        break;


    case 'visite':

        $url_ricevuto = $_POST["url"];

        $month = date("m");
        $year = date("Y");

        $result = Query($conn, "SELECT * FROM PWS_Interactions WHERE (month, year) IN (($month,$year))", $return_obj);

        // If month-year row hasn't been created yet
        if ($result->num_rows == 0) Query($conn, "INSERT INTO PWS_Interactions (month, year) VALUES ($month,$year)", $return_obj);

        // for ($i = 0; $i < count($_POST["url"]); $i++) {
        //     # code...
        //     $url_ricevuto = $_POST["url"][$i];
        $result = Query($conn, "SELECT id_page FROM PWS_Pages WHERE url='$url_ricevuto' limit 1", $return_obj);
        $id_page = $result->fetch_array(MYSQLI_ASSOC)['id_page'];
        $id_page_ = $id_page . '_';

        // If page has never been registrer before on database
        if ($result->num_rows == 0) {
            // Create record in PWS_Pages table
            Query($conn, "INSERT INTO PWS_Pages (url) VALUES ('$url_ricevuto')", $return_obj);

            $result = Query($conn, "SELECT id_page FROM PWS_Pages WHERE url='$url_ricevuto' limit 1", $return_obj);
            $id_page = $result->fetch_array(MYSQLI_ASSOC)['id_page'];
            $id_page_ = $id_page . '_';

            // Add column to PWS_Interactions
            Query($conn, "ALTER TABLE PWS_Interactions ADD COLUMN $id_page_ INT DEFAULT 0", $return_obj);
        }

        Query($conn, $sql = "UPDATE PWS_Pages SET interactions=interactions+1 WHERE id_page=$id_page", $return_obj);
        Query($conn, "UPDATE PWS_Interactions SET $id_page_=$id_page_+1 WHERE (month, year) IN (($month,$year))", $return_obj);
        // }

        break;

    default:
        # code...
        break;
}

$conn->close();
if ($debug) $return_obj->Log[] = "Connection with MySQL database closed";

$return_obj->Result->Status = 0;
returndata($return_obj);

<?php
include "setting.php";

isData(["email", "pwd"], $return_obj);

$email = strtolower($_POST["email"]);
$pwd = $_POST["pwd"];

if ($debug) {
    $return_obj->Log[] = "E-mail ricevuta: $email";
    $return_obj->Log[] = "Password ricevuta: $pwd";
}


$sql = "SELECT * FROM PWS_Users WHERE email = '$email'";

if (!$result = $conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

if ($result->num_rows) {
    $row = $result->fetch_array(MYSQLI_ASSOC);
    if ($row['pwd'] == $pwd) {
        if ($row['verificato'] == 1) {

            $return_obj->Result->Color = $row['colore'];
            $return_obj->Result->Font = $row['font'];
            $return_obj->Result->Avatar = $row['avatar'];
            $return_obj->Result->Nickname = $row['nickname'];
            $return_obj->Result->Email = $row['email'];
            $return_obj->Result->Password = $row['pwd'];
        } else {
            $return_obj->DataReceived_err[] = "Devi ancora verficare l'email";
            die(returndata($return_obj));
        }
    } else {
        $return_obj->DataReceived_err[] = "La pwd è sbagliata";
        die(returndata($return_obj));
    }
} else {
    $return_obj->DataReceived_err[] = "Non esite il l'utente";
    die(returndata($return_obj));
}

$conn->close();
if ($debug) {
    $return_obj->Log[] = "Connection with MySQL database closed";
}

$return_obj->Result->Status = 1;
returndata($return_obj);

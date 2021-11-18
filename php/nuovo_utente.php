<?php
include "setting.php";

isData(["nickname", "email", "pwd"], $return_obj);

$nickname = $_POST["nickname"];

$email = strtolower($_POST["email"]);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $return_obj->DataReceived_err[] = "Invalid email format";
    die(returndata($return_obj));
}

$pwd = $_POST["pwd"];
if (strlen($pwd) < 5) {
    $return_obj->DataReceived_err[] = "Password must be longer than 5 characters";
    die(returndata($return_obj));
}

$newsletter = ($_POST["newsletter"] == 'on') ? 1 : 0;

if ($debug) {
    $return_obj->Log[] = "Nickname ricevuto: $nickname";
    $return_obj->Log[] = "E-mail ricevuta: $email";
    $return_obj->Log[] = "Password ricevuta: $pwd";
    $return_obj->Log[] = "Opzione newsletter: $newsletter";
}


$sql = "CREATE TABLE IF NOT EXISTS Utenti (
    nickname VARCHAR(127),
    email VARCHAR(127),
    pwd VARCHAR(127),
    colore VARCHAR(127) DEFAULT 'rgb(255, 165, 0)',
	font INT(2) DEFAULT 0,
    newsletter BOOLEAN DEFAULT 0,

    num_accessi INT(5) DEFAULT 1,
    verificato BOOLEAN DEFAULT 0,
    creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_accesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE (email),
    PRIMARY KEY (nickname))
    ENGINE=InnoDB";

if (!$conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

$sql = "INSERT INTO Utenti (nickname, email, pwd, newsletter) VALUES ('$nickname','$email','$pwd','$newsletter')";
if (!$conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

$conn->close();
if ($debug) {
    $return_obj->Log[] = "Connection with MySQL database closed";
}


$message .= "<h1 style='text-align:center;font-size:25px;margin-bottom:20px'>Benvenuto " . $nickname . "!<h1><p style='text-align:left;font-size:15px'>Grazie per aver deciso di iscriverti al mio sito e quindi di supportarmi in quello che faccio :).<br>
Con questo messaggio voglio semplicemente verificare che l'email inserita nel modulo del sito sia corretta.<br><br>
Clicca su <a href='https://bocchioutils.altervista.org/php/verifica_email.php?email=". $email ."'>questo link</a> per confermarla.<br><br>
Se pensi che questa email abbia avuto un destinatario erroneo, ignorala.<br><br>
Il WebMaster di Bocchio's WebSite,<br>
Tommaso<br></p>";

if (mail($email, $subject, $message .= "</body> </html>", $headers)) {
    $return_obj->Log[] = "Email successfully sent to $email";
} else {
    $return_obj->EmailSender_err[] = "Email sending to $email failed";
    die(returndata($return_obj));
}
$return_obj->Result->Status = 1;
returndata($return_obj);

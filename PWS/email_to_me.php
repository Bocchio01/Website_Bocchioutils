<?php

include "setting.php";
$email = 'tommaso.bocchietti@gmail.com';
$nickname = 'Bocchio01';

$message .= "<h1 style='text-align:center;font-size:25px;margin-bottom:20px'>Benvenuto " . $nickname . "!<h1><p style='text-align:left;font-size:15px'>Grazie per aver deciso di iscriverti al mio sito e quindi di supportarmi in quello che faccio :).<br>
Con questo messaggio voglio semplicemente verificare che l'email inserita nel modulo del sito sia corretta.<br><br>
Clicca su <a href='https://bocchioutils.altervista.org/php/verifica_email.php?email=". $email ."'>questo link</a> per confermarla.<br><br>
Se pensi che questa email abbia avuto un destinatario erroneo, ignorala.<br><br>
Il WebMaster di Bocchio's WebSite,<br>
Tommaso<br></p>";

mail($email, $subject, $message .= "</body> </html>", $headers);

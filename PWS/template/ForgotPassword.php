<html>

<head>

    <style>
        h1 {
            text-align: center;
            font-size: 25px;
            margin-bottom: 20px;
        }

        p {
            font-size: 15px;
        }
    </style>

</head>

<body>
    <h1>Recupero/cambio credenziali!</h1>
    <p>
        Ciao, se hai ricevuto questa email vuol dire che qualcuno ha richiesto un 'recupero password' per l'account creato sul sito 'Bocchio's WebSite' collegato a questa email.<br>
        Se non sei stato tu a richiedere questo servizio, sarebbe oppurtuno contattassi <a href="mailto:tommaso.bocchietti@gmail.com?subject=Recupero credenziali sito inaspettato&body=Richiesta effettuata per l'account legato all'email: <?php echo $email ?>">l'amministrazione del sito</a><br><br>
        In caso contrario invece, clicca su <a href='https://bocchioutils.altervista.org/PWS/LandingPage.php?action=ForgotPassword&token=<?php echo $token ?>'>questo link</a> e potrai generare una nuova password.<br><br>
        ATTENZIONE!<br>
        Il link sopra è valevole solo una volta. Nel caso volessi recuperare/cambiare nuovamente la tua password dovrai ri-chiederla dal sito 'Bocchio's WebSite' tramite la schermata di login.<br>
        Per ragioni di sicurezza, una volta cliccato sul link sopra sarai disconnesso dal tuo account da ogni dispositivo e dovrai ri-loggarti nel sito.<br><br>
        Il WebMaster di Bocchio's WebSite,<br>
        Tommaso<br>
    </p>
</body>

</html>
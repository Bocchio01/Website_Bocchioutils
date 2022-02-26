<html>

<head>

    <?php echo $style ?>

</head>

<body>
    <h1>Recupero/cambio credenziali!</h1>
    <p>
        Ciao, se hai ricevuto questa email vuol dire che qualcuno ha richiesto un "recupero password" per l'account creato sul sito "Bocchio's WebSite" collegato a questa email.<br>
        Se non sei stato tu a richiedere questo servizio, sarebbe oppurtuno contattassi <a href="mailto:tommaso.bocchietti@gmail.com?subject=Recupero credenziali sito inaspettato&body=Richiesta effettuata per l'account legato all'email: <?php echo $email ?>">l'amministrazione del sito</a><br><br>
        In caso contrario invece, clicca su <a href='https://it.bocchioutils.altervista.org/BWS/site/?action=ModifyPasswordEmail&email=<?php echo $email ?>'>questo link</a> e potrai generare una nuova password.<br><br>
        Nel campo "Password provvisoria" del modulo, inserisci: <?php echo $tmp ?><br><br>
        Il WebMaster di Bocchio's WebSite,<br>
        Tommaso<br>
    </p>
</body>

</html>
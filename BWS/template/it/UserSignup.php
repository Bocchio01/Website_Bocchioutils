<html>

<head>

    <style>
        <?php echo $style ?>
    </style>

</head>

<body>
    <h1>Benvenuto <?php echo $nickname ?>!</h1>
    <p>
        Grazie per aver deciso di iscriverti al mio sito e quindi di supportarmi in quello che faccio :).<br>
        Con questo messaggio voglio semplicemente verificare che l'email inserita nel modulo del sito sia corretta.<br><br>
        Clicca su <a href='<?php echo UTILS_SITE ?>/BWS/site/?l=it&action=VerifyEmail&tmp=<?php echo $tmp ?>'>questo link</a> per confermarla.<br><br>
        Se pensi che questa email abbia avuto un destinatario erroneo, ignorala.<br><br>
        Il WebMaster di Bocchio's WebSite,<br>
        Tommaso<br>
    </p>
</body>

</html>
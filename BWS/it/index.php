<?php

require_once "../_utils/_index.php";

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Tommaso Bocchietti">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bocchio's WebSite Utils</title>
    <style>
        @import url("../../style.css");

        .graph {
            max-width: 600px;
        }

        .graph>img {
            width: 300px;
            max-width: 100%;
        }
    </style>
</head>

<body>

    <header>
        <div>
            <h1><a href="./">Bocchio's WebSite Utils</a></h1>
            <a href="../en/"><img src="/_langflag/it.png" alt="Bandiera IT"></a>
        </div>
        <hr>
    </header>

    <main>

        <div class="data">

            <?php if ($action != -1) : ?>

                <div class="card graph">

                <?php endif; ?>

                <?php switch ($action):

                    case 'VerifyEmail':
                        switch ($error):
                            case 0: ?>
                                <h2>Perfetto!</h2>
                                <p>Email verificata correttamente!</p>
                                <a href=<?php echo HOST_URL ?> rel="noopener noreferrer">Clicca qui per andare direttamente al sito principale ed usufruire delle funzionalità aggiuntive.</a>
                            <?php break;
                            case 1: ?>
                                <h2>Email già verificata!</h2>
                                <a href=<?php echo HOST_URL ?> rel="noopener noreferrer">Clicca qui per andare direttamente al sito principale ed usufruire delle funzionalità aggiuntive.</a>
                            <?php break;
                            case 2: ?>
                                <h2>Errore..</h2>
                                <p>C'é stato un problema durante la verifica dell'email.</p>
                                <a href="mailto:tommaso.bocchietti@gmail.com?subject=Verifica email non riuscita" rel="noopener noreferrer">Clicca qui per scrivere all'amministrazione del sito.</a>
                            <?php endswitch;
                        break;


                    case 'ForgotPassword':
                        switch ($error):
                            case 0: ?>
                                <h2>Controlla la tua email!</h2>
                                <p>L'email con le istruzioni per il recupero credenziali è appena stata inviata a: <?php echo $email ?></p>
                            <?php break;
                            case 1: ?>
                                <h2>Errore..</h2>
                                <p>C'è stato un problema durante l'invio dell'email all'indirizzo: <?php echo $email ?></p>
                                <a href="">Riprova.</a>
                            <?php break;
                            case 2: ?>
                                <h2>Email inserita non corretta</h2>
                                <a href="">L'email inserita non è associata a nessun utente. Riprova.</a>
                            <?php break;
                            default: ?>
                                <h2>Password dimenticata</h2>
                                <p>Per ragioni di sicurezza e privacy, conserviamo le password sul server solamente criptate, per questo motivo non possiamo fornirti direttamente la tua password.</p>
                                <p>Inserisci la tua email nel campo di seguito e clicca su invia. Riceverai per email le istruzioni per reimpostare la tua password</p>
                                <form method="post">
                                    <div>
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email">
                                    </div>
                                    <input type="submit" name="submit" value="Invia">
                                </form>
                            <?php endswitch;
                        break;


                    case 'ModifyPassword':
                        switch ($error):
                            case 0: ?>
                                <h2>Password modificata!</h2>
                                <p>Il cambio password ha avuto esito positivo.</p>
                                <a href=<?php echo HOST_URL ?> rel="noopener noreferrer">Riaccedi al sito con la tua nuova password!</a>
                            <?php break;
                            case 1: ?>
                                <h2>Errore..</h2>
                                <a href="">Controlla i dati inseriti.</a>
                            <?php break;
                            default: ?>
                                <h2>Modifica password</h2>
                                <p>Inserisci qui la tua email e la tua vecchia password.</p>
                                <p>Nel campo apposito inserisci la nuova password e poi clicca su invia.</p>
                                <form method="post">
                                    <div>
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email">
                                        <label for="old_password">Vecchia password</label>
                                        <input type="text" name="old_password" id="old_password">
                                        <label for="new_password">Nuova password</label>
                                        <input type="text" name="new_password" id="new_password" minlength="5">
                                    </div>
                                    <input type="submit" name="submit" value="Invia">
                                </form>
                            <?php endswitch;
                        break;


                    case 'ModifyPasswordEmail':
                        switch ($error):
                            case 0: ?>
                                <h2>Password modificata!</h2>
                                <p>Il cambio password ha avuto esito positivo.</p>
                                <a href=<?php echo HOST_URL ?> rel="noopener noreferrer">Riaccedi al sito con la tua nuova password!</a>
                            <?php break;
                            case 1: ?>
                                <h2>Errore..</h2>
                                <a href="">Controlla i dati inseriti.</a>
                            <?php break;
                            case 2: ?>
                                <h2>Errore..</h2>
                                <p>La nuova passord deve essere lunga almeno 5 caratteri.</p>
                                <a href="">Riprova.</a>
                            <?php break;
                            default: ?>
                                <h2>Modifica password</h2>
                                <p>Inserisci qui la tua email e la password provvisoria ricevuta.</p>
                                <p>Nel campo apposito inserisci la nuova password e poi clicca su invia.</p>
                                <form method="post">
                                    <div>
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email" value=<?php if (isset($_GET['email'])) echo $_GET['email']; ?>>
                                        <label for="tmp">Password provvisoria</label>
                                        <input type="text" name="tmp" id="tmp">
                                        <label for="new_password">Nuova password</label>
                                        <input type="text" name="new_password" id="new_password">
                                    </div>
                                    <input type="submit" name="submit" value="Invia">
                                </form>
                        <?php endswitch;
                        break;


                    default: ?>
                        <a href="?action=ForgotPassword">
                            <div class="card graph">
                                <h2>Password dimenticata</h2>
                                <img src="https://my.host.it/image/recupera-password.jpg" alt="Password dimenticata">
                            </div>
                        </a>

                        <a href="?action=ModifyPassword">
                            <div class="card graph">
                                <h2>Modifica password</h2>
                                <img src="https://my.host.it/image/recupera-password.jpg" alt="Modifica password">
                            </div>
                        </a>

                        <hr style="width: 80%">

                        <a href="Analytics.php">
                            <div class="card graph">
                                <h2>Analytics</h2>
                                <img src="https://my.host.it/image/recupera-password.jpg" alt="Analytics">
                            </div>
                        </a>

                        <a href="Database.php">
                            <div class="card graph">
                                <h2>Database</h2>
                                <img src="https://my.host.it/image/recupera-password.jpg" alt="Analytics">
                            </div>
                        </a>

                <?php endswitch; ?>

                <?php if ($action != -1) : ?>

                </div>

            <?php endif; ?>

    </main>

    <footer>
        <hr>
        <h2 id="copyright"></h2>
    </footer>

</body>

<script>
    document.getElementById('copyright').innerText = "Tommaso Bocchietti @ " + new Date().getFullYear();
</script>

</html>
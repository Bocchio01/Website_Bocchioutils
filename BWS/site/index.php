<?php

include "../../_setting.php";
header('Content-Type: text/html; charset=utf-8');

list($i18n, $locale, $notlocale) = LoadTranslation();
$i18n = $i18n['index'];

$action = -1;

if (isset($_GET['action'])) {
    $error = -1;
    $action = $_GET['action'];
    switch ($action) {

        case 'VerifyEmail':
            if (isset($_GET["tmp"])) {
                $tmp = $_GET["tmp"];

                if (Query("SELECT verified FROM BWS_Users WHERE (tmp, verified) = ('$tmp', 0)")->num_rows) {
                    Query("UPDATE BWS_Users SET verified=1, tmp=NULL WHERE tmp='$tmp'");
                    $error = 0;
                } else $error = 1;
            } else $error = 2;
            break;


        case 'ForgotPassword':
            if (isset($_POST["submit"])) {
                $email = strtolower($_POST["email"]);

                if ($id_user = Query("SELECT id_user FROM BWS_Users WHERE email='$email'")->fetch_array(MYSQLI_ASSOC)['id_user']) {
                    $tmp = CreateToken(5);
                    Query("UPDATE BWS_Users SET tmp='$tmp' WHERE id_user='$id_user'");

                    $message = render('../template/' . $locale . '/ForgotPassword.php', array('tmp' => $tmp, 'email' => $email));

                    if (mail($email, $subject, $message, $headers)) $error = 0;
                    else $error = 1;
                } else $error = 2;
            }
            break;

        case 'ModifyPassword':
            if (isset($_POST["submit"])) {
                $email = strtolower($_POST["email"]);
                $old_password = md5($_POST["old_password"]);
                $new_password = md5($_POST["new_password"]);

                if ($id_user = Query("SELECT id_user FROM BWS_Users WHERE (email, password)=('$email', '$old_password')")->fetch_array(MYSQLI_ASSOC)['id_user']) {
                    $token = CreateToken();

                    Query("UPDATE BWS_Users SET password='$new_password', token='$token' WHERE id_user='$id_user'");
                    $error = 0;
                } else $error = 1;
            }
            break;

        case 'ModifyPasswordEmail':
            if (isset($_POST["submit"])) {
                $email = strtolower($_POST["email"]);
                $tmp = $_POST["tmp"];
                if (strlen($_POST["new_password"]) >= 5) {

                    $new_password = md5($_POST["new_password"]);

                    if ($id_user = Query("SELECT id_user FROM BWS_Users WHERE (email, tmp)=('$email', '$tmp')")->fetch_array(MYSQLI_ASSOC)['id_user']) {
                        $token = CreateToken();

                        Query("UPDATE BWS_Users SET password='$new_password', tmp=NULL, token='$token' WHERE id_user='$id_user'");
                        $error = 0;
                    } else $error = 1;
                } else $error = 2;
            }
            break;


        default:
            $action = -1;
            break;
    }
}

$conn->close();
unset($_POST);

?>

<!DOCTYPE html>
<html lang=<?= $locale ?>>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Tommaso Bocchietti">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title><?= $i18n['title'] ?></title>
    <style>
        @import url("../../style.css");

        .graph {
            max-width: 600px;
        }

        .graph>img {
            width: 300px;
            max-width: 100%;
        }

        @media (max-width: 800px) {
            .graph>img {
                width: 200px;
            }
        }
    </style>
</head>

<body>

    <header>
        <div>
            <h1><a href="./?l=<?= $locale ?>"><?= $i18n['title'] ?></a></h1>
            <a href="./?l=<?= $notlocale ?>"><img src="/_img/lang/<?= $notlocale ?>.png" alt="Bandiera <?= $notlocale ?>"></a>
        </div>
        <p style="display: block; text-align:center; margin:0"><a href=<?php echo HOST_URL; ?>><?= $i18n['subtitle'] ?></a></p>
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
                                <h2><?= $i18n['VerifyEmail'][0]['h2'] ?></h2>
                                <p><?= $i18n['VerifyEmail'][0]['p'] ?></p>
                                <a href=<?php echo HOST_URL ?> rel="noopener noreferrer"><?= $i18n['VerifyEmail'][0]['a'] ?></a>
                            <?php break;
                            case 1: ?>
                                <h2><?= $i18n['VerifyEmail'][1]['h2'] ?></h2>
                                <a href=<?php echo HOST_URL ?> rel="noopener noreferrer"><?= $i18n['VerifyEmail'][1]['a'] ?></a>
                            <?php break;
                            case 2: ?>
                                <h2><?= $i18n['VerifyEmail'][2]['h2'] ?></h2>
                                <p><?= $i18n['VerifyEmail'][2]['p'] ?></p>
                                <a href="mailto:webmaster@bocchio.dev?<?= $i18n['VerifyEmail'][2]['a_query'] ?>" rel="noopener noreferrer"><?= $i18n['VerifyEmail'][2]['a'] ?></a>
                            <?php endswitch;
                        break;


                    case 'ForgotPassword':
                        switch ($error):
                            case 0: ?>
                                <h2><?= $i18n['ForgotPassword'][0]['h2'] ?></h2>
                                <p><?= $i18n['ForgotPassword'][0]['p'] ?><?php echo $email ?></p>
                            <?php break;
                            case 1: ?>
                                <h2><?= $i18n['ForgotPassword'][1]['h2'] ?></h2>
                                <p><?= $i18n['ForgotPassword'][1]['p'] ?><?php echo $email ?></p>
                                <a href="?l=<?= $locale ?>"><?= $i18n['ForgotPassword'][1]['a'] ?></a>
                            <?php break;
                            case 2: ?>
                                <h2><?= $i18n['ForgotPassword'][2]['h2'] ?></h2>
                                <a href="?l=<?= $locale ?>"><?= $i18n['ForgotPassword'][2]['a'] ?></a>
                            <?php break;
                            default: ?>
                                <h2><?= $i18n['ForgotPassword'][3]['h2'] ?></h2>
                                <p><?= $i18n['ForgotPassword'][3]['p'][0] ?></p>
                                <p><?= $i18n['ForgotPassword'][3]['p'][1] ?></p>
                                <form method="post">
                                    <div>
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email">
                                    </div>
                                    <input type="submit" name="submit" value=<?= $i18n['ForgotPassword'][3]['submit'] ?>>
                                </form>
                            <?php endswitch;
                        break;


                    case 'ModifyPassword':
                        switch ($error):
                            case 0: ?>
                                <h2><?= $i18n['ModifyPassword'][0]['h2'] ?></h2>
                                <p><?= $i18n['ModifyPassword'][0]['p'] ?></p>
                                <a href=<?php echo HOST_URL ?> rel="noopener noreferrer"><?= $i18n['ModifyPassword'][0]['a'] ?></a>
                            <?php break;
                            case 1: ?>
                                <h2><?= $i18n['ModifyPassword'][1]['h2'] ?></h2>
                                <a href="?l=<?= $locale ?>"><?= $i18n['ModifyPassword'][1]['a'] ?></a>
                            <?php break;
                            default: ?>
                                <h2><?= $i18n['ModifyPassword'][2]['h2'] ?></h2>
                                <p><?= $i18n['ModifyPassword'][2]['p'][0] ?></p>
                                <p><?= $i18n['ModifyPassword'][2]['p'][1] ?></p>
                                <form method="post">
                                    <div>
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email">
                                        <label for="old_password"><?= $i18n['ModifyPassword'][2]['form'][0] ?></label>
                                        <input type="text" name="old_password" id="old_password">
                                        <label for="new_password"><?= $i18n['ModifyPassword'][2]['form'][1] ?></label>
                                        <input type="text" name="new_password" id="new_password" minlength="5">
                                    </div>
                                    <input type="submit" name="submit" value=<?= $i18n['ModifyPassword'][2]['form'][2] ?>>
                                </form>
                            <?php endswitch;
                        break;


                    case 'ModifyPasswordEmail':
                        switch ($error):
                            case 0: ?>
                                <h2><?= $i18n['ModifyPassword'][0]['h2'] ?></h2>
                                <p><?= $i18n['ModifyPassword'][0]['p'] ?></p>
                                <a href=<?php echo HOST_URL ?> rel="noopener noreferrer"><?= $i18n['ModifyPassword'][0]['a'] ?></a>
                            <?php break;
                            case 1: ?>
                                <h2><?= $i18n['ModifyPassword'][1]['h2'] ?></h2>
                                <a href="?l=<?= $locale ?>"><?= $i18n['ModifyPassword'][1]['a'] ?></a>
                            <?php break;
                            case 2: ?>
                                <h2><?= $i18n['ModifyPasswordEmail'][0]['h2'] ?></h2>
                                <p><?= $i18n['ModifyPasswordEmail'][0]['p'] ?></p>
                                <a href="?l=<?= $locale ?>"><?= $i18n['ModifyPasswordEmail'][0]['a'] ?></a>
                            <?php break;
                            default: ?>
                                <h2><?= $i18n['ModifyPasswordEmail'][1]['h2'] ?></h2>
                                <p><?= $i18n['ModifyPasswordEmail'][1]['p'][0] ?></p>
                                <p><?= $i18n['ModifyPasswordEmail'][1]['p'][1] ?></p>
                                <form method="post">
                                    <div>
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email" value=<?php if (isset($_GET['email'])) echo $_GET['email']; ?>>
                                        <label for="tmp"><?= $i18n['ModifyPasswordEmail'][1]['form'][0] ?></label>
                                        <input type="text" name="tmp" id="tmp">
                                        <label for="new_password"><?= $i18n['ModifyPasswordEmail'][1]['form'][1] ?></label>
                                        <input type="text" name="new_password" id="new_password">
                                    </div>
                                    <input type="submit" name="submit" value=<?= $i18n['ModifyPasswordEmail'][1]['form'][2] ?>>
                                </form>
                        <?php endswitch;
                        break;


                    default: ?>
                        <a href="?l=<?= $locale ?>&action=ForgotPassword">
                            <div class="card graph hover">
                                <h2><?= $i18n['default'][0] ?></h2>
                                <img src="../../_img/index/ForgotPassword.svg" alt=<?= $i18n['default'][0] ?>>
                            </div>
                        </a>

                        <a href="?l=<?= $locale ?>&action=ModifyPassword">
                            <div class="card graph hover">
                                <h2><?= $i18n['default'][1] ?></h2>
                                <img src="../../_img/index/ModifyPassword.svg" alt=<?= $i18n['default'][1] ?>>
                            </div>
                        </a>

                        <hr style="width: 80%">

                        <a href="analytics.php?l=<?= $locale ?>&isStatistics=1">
                            <div class="card graph hover">
                                <h2><?= $i18n['default'][2] ?></h2>
                                <img src="../../_img/index/Analytics.svg" alt=<?= $i18n['default'][2] ?>>
                            </div>
                        </a>

                        <a href="database.php?l=<?= $locale ?>">
                            <div class="card graph hover">
                                <h2><?= $i18n['default'][3] ?></h2>
                                <img src="../../_img/index/Database.svg" alt=<?= $i18n['default'][3] ?>>
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
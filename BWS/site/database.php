<?php

require_once "../../_isAdmin.php";
header('Content-Type: text/html; charset=utf-8');

list($i18n, $locale, $notlocale) = LoadTranslation();
$i18n = $i18n['database'];

?>

<!DOCTYPE html>
<html lang=<?= $locale ?>>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Tommaso Bocchietti">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $i18n['title'] ?></title>
    <style>
        @import url("../../style.css");

        main {
            text-align: center;
            /* overflow: auto; */
            display: block !important;
        }

        table {
            display: block;
            overflow: auto;
            border-width: 0px;
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

        <?php if ($login != 1) : ?>

            <div class="data">
                <div class="card graph">
                    <h2><?= $i18n['h2'] ?></h2>
                    <p><?= $i18n['p'] ?></p>
                    <form method="post">
                        <div>
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password">
                        </div>
                        <input type="submit" name="submit" value="Submit">
                    </form>
                </div>
            </div>

            <?php else :

            $tables = array('BWS_Users', 'BWS_Pages', 'BWS_Forum', 'BWS_Translations', 'BWS_Interactions');

            foreach ($tables as $table) :
                $return = Query("SELECT * FROM $table"); ?>
                <h2> <?php echo $table ?></h2>
                <table>
                    <thead>
                        <tr>
                            <?php while ($fieldinfo = $return->fetch_field()) : ?>
                                <th> <?php echo $fieldinfo->name ?></th>
                            <?php endwhile; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $return->fetch_array(MYSQLI_ASSOC)) : ?>
                            <tr>
                                <?php foreach ($row as $cell) : ?>
                                    <td> <?php echo htmlentities($cell) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
        <?php endforeach;

        endif; ?>

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
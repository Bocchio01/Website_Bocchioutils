<?php

require_once "../_utils/_analytics.php";

?>

<!DOCTYPE HTML>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Tommaso Bocchietti">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bocchio's WebSite Analytics</title>
    <style>
        @import url("../../style.css");
    </style>
</head>

<body>
    <header>
        <div>
            <h1><a href="./">Bocchio's WebSite Analytics</a></h1>
            <a href="../en/"><img src="/_langflag/it.png" alt="Bandiera IT"></a>
        </div>
        <hr>
    </header>
    <main>

        <form method="GET">
            <div class="card">

                <label for="stat">
                    <input id="stat" name="stat" type="checkbox" value="1" <?php if (isset($stat)) : ?> checked <?php endif; ?>>
                    Visualizza statistiche
                </label>

                <hr>

                <label for="registrazioni">
                    <input id="registrazioni" name="registrazioni" type="checkbox" value="1" <?php if (isset($registrazioni)) : ?> checked <?php endif; ?>>
                    Visualizza registrazioni
                </label>

                <hr>

                <label for="lingue">
                    <input id="lingue" name="lingue" type="checkbox" value="1" <?php if (isset($lingue)) : ?> checked <?php endif; ?>>
                    Visualizza lingue
                </label>

                <hr>

                <label for="anni">
                    <input id="anni" name="anni" type="checkbox" value="1" <?php if (isset($anni)) : ?> checked <?php endif; ?>>
                    Visualizza anni
                </label>

                <hr>

                <label for="id_pagina">Seleziona una pagina</label>
                <select id="id_pagina" name="id_pagina">
                    <option></option>
                    <?php foreach ($data_pages as $name => $id_page) : ?>
                        <option value="<?php echo $id_page ?>" <?php if (isset($id_pagina) && $id_page == $id_pagina) : ?> selected="selected" <?php endif; ?>><?php echo $name ?></option>
                    <?php endforeach; ?>
                </select>

                <hr>

                <label for="tipo_pagina">Seleziona una tipologia</label>
                <select id="tipo_pagina" name="tipo_pagina">
                    <option></option>
                    <?php foreach ($type_page as $type) : ?>
                        <option value="<?php echo $type ?>" <?php if (isset($tipo_pagina) && $type == $tipo_pagina) : ?> selected="selected" <?php endif; ?>><?php echo $type ?></option>
                    <?php endforeach; ?>

                </select>

                <hr>

                <label for="lingua">Seleziona una lingua</label>
                <select id="lingua" name="lingua">
                    <option></option>
                    <?php foreach ($lang as $l) : ?>
                        <option value="<?php echo $l ?>" <?php if (isset($lingua) && $l == $lingua) : ?> selected="selected" <?php endif; ?>><?php echo $l ?></option>
                    <?php endforeach; ?>

                </select>

                <hr>

                <label for="anno_partenza">Anno partenza</label>
                <select id="anno_partenza" name="anno_partenza">
                    <option></option>
                    <?php for ($i = 2022; $i <= $year; $i++) : ?>
                        <option value="<?php echo $i ?>" <?php if (isset($anno_partenza) && $i == $anno_partenza) : ?> selected="selected" <?php endif; ?>><?php echo $i ?></option>
                    <?php endfor; ?>

                </select>

            </div>

            <input type="submit" name="submit" value="Genera">
        </form>

        <hr class="spacer">

        <div class="data">

            <?php if (isset($stat)) : ?>
                <div class="card number">


                    <?php foreach ($stats as $s => $value) : ?>
                        <div>
                            <h2><?php echo $s ?></h2>

                            <?php foreach ($stats[$s] as $sv => $svalue) : ?>
                                <div><?php echo $svalue ?></div>
                            <?php endforeach; ?>

                        </div>

                        <?php if ($s != array_key_last($stats)) : ?>
                            <hr class="spacer">
                        <?php endif; ?>

                    <?php endforeach; ?>

                    <!-- <hr class="spacer">

                <div>
                    <h2>Legenda colori</h2>
                    <?php foreach ($lang_ as $l => $value) : ?>
                        <div style="background-color: <?php echo $color_list[$l] ?>"><?php echo $value ?></div>
                    <?php endforeach; ?>
                </div> -->
                </div>
            <?php endif; ?>


            <?php foreach ($plots as $plot => $value) : ?>
                <div class="card graph">
                    <h2><?php echo $plot ?></h2>
                    <img src="<?php echo $value->EncodeImage() ?>" alt=<?php echo $plot ?>>
                </div>
            <?php endforeach; ?>

        </div>

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
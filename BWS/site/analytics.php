<?php

include_once "../../_setting.php";

header('Content-Type: text/html; charset=utf-8');
require_once '../../_lib/phplot-6.2.0/phplot.php';


function DefaultGraph($data, $type = 'linepoints')
{
    global $lang_;
    $plot = new PHPlot();

    $plot->SetFailureImage(False);
    $plot->SetPrintImage(False);
    $plot->SetDataValues($data);
    $plot->SetPlotType($type);
    $plot->SetBackgroundColor('#fdfdff');
    $plot->SetTransparentColor('#fdfdff');
    $plot->SetFontGD('x_label', 3);
    $plot->SetFontGD('y_label', 3);
    $plot->SetFontGD('generic', 3);
    $plot->SetFontGD('y_title', 4);
    $plot->SetFontGD('x_title', 4);
    $plot->SetPointShapes('dot');
    $plot->SetLineStyles('solid');

    // $plot->SetXLabelType('time');
    $plot->SetXTickLabelPos('none');
    $plot->SetXTickPos('none');
    $plot->SetLineWidths(3);
    // $plot->SetYTickIncrement(1);

    $plot->SetLegend($lang_);

    return $plot;
}

$year = (int) date("Y");
$month = (int) date("m") - 1;

$plots = array();
extract($_GET);


$lang_ = empty($lingua) ? $lang : array($lingua);
$start_year = empty($anno_partenza) ? "2022" : $anno_partenza;


// Dati generali
$result = Query("SELECT id_page, CONCAT(name, ' - (' , type, ')') AS descr FROM BWS_Pages ORDER BY type");
while ($row = $result->fetch_array(MYSQLI_ASSOC)) $data_pages[$row['descr']] = $row['id_page'];


// Registrazioni al sito
if (!empty($registrazioni)) {
    foreach ($lang_ as $l => $val) $data[$val] = 0;


    $current_year = $start_year;
    $ser = "'" . implode("','", $lang_) . "'";

    while ($current_year <= $year) {
        if ($current_year == $year) $num = $month;
        else $num = 12;

        for ($i = 0; $i <= $num; $i++) {

            $result = Query("SELECT COUNT(id_user) AS num, lang FROM BWS_Users WHERE lang IN ($ser) AND DATE_FORMAT(creation_date, '%m-%Y') = '" . sprintf('%02s-%s', $i + 1, $current_year) . "' GROUP BY lang ORDER BY creation_date");
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) $data[$row['lang']] += $row['num'];

            $set = array();
            $set[] = $i + 1 . '-' . $current_year;
            foreach ($lang_ as $l => $val) $set[] = $data[$val];
            $example_data[] = $set;
        }

        $current_year += 1;
    }


    $plots['Registrazioni al sito'] = DefaultGraph($example_data);
    end($plots)->SetYTitle('Attualmente registrati');
    end($plots)->DrawGraph();
    $example_data = null;
}



// Andamento: Gorlu la stampante
if (!empty($id_pagina)) {

    $current_year = $start_year;

    while ($current_year <= $year) {
        if ($current_year == $year) $num = $month;
        else $num = 12;

        for ($i = 0; $i <= $num; $i++) {

            $sql = array();
            foreach ($lang_ as $l => $val) $sql[] = "SUM(JSON_EXTRACT(`$current_year`, '$[$i].$val')) AS $val";
            $row = Query("SELECT " . implode(', ', $sql) . " FROM BWS_Interactions WHERE id_page = $id_pagina")->fetch_array(MYSQLI_ASSOC);
            foreach ($row as $key => $value) $row[$key] = ($value == null) ? 0 : $value;

            $set = array();
            $set[] = $i + 1 . '-' . $current_year;
            foreach ($lang_ as $l => $val) $set[] = $row[$val];
            $example_data[] = $set;
        }

        $current_year += 1;
    }

    $plots['Andamento: ' . array_search($id_pagina, $data_pages)] = DefaultGraph($example_data);
    end($plots)->SetYTitle('Numero visite per mese');
    end($plots)->DrawGraph();
    $example_data = null;
}



// Andamento: Mix / Articoli...
if (!empty($tipo_pagina)) {
    $id_array = Query("SELECT GROUP_CONCAT(id_page) AS id_array FROM BWS_Pages WHERE type = '$tipo_pagina'")->fetch_array(MYSQLI_ASSOC)['id_array'];

    $current_year = $start_year;

    while ($current_year <= $year) {
        if ($current_year == $year) $num = $month;
        else $num = 12;

        for ($i = 0; $i <= $num; $i++) {

            $sql = array();
            foreach ($lang_ as $l => $val) $sql[] = "SUM(JSON_EXTRACT(`$current_year`, '$[$i].$val')) AS $val";
            $row = Query("SELECT " . implode(', ', $sql) . " FROM BWS_Interactions WHERE id_page IN ($id_array)")->fetch_array(MYSQLI_ASSOC);
            foreach ($row as $key => $value) $row[$key] = ($value == null) ? 0 : $value;

            $set = array();
            $set[] = $i + 1 . '-' . $current_year;
            foreach ($lang_ as $l => $val) $set[] = $row[$val];
            $example_data[] = $set;
        }

        $current_year += 1;
    }

    $plots['Andamento: ' . $tipo_pagina] = DefaultGraph($example_data);
    end($plots)->SetYTitle('Numero visite per mese');
    end($plots)->DrawGraph();
    $example_data = null;
}



// Lingue
if (!empty($lingue)) {
    $current_year = $start_year;

    while ($current_year <= $year) {
        if ($current_year == $year) $num = $month;
        else $num = 12;

        for ($i = 0; $i <= $num; $i++) {

            $sql = array();
            foreach ($lang_ as $l => $val) $sql[] = "SUM(JSON_EXTRACT(`$current_year`, '$[$i].$val')) AS $val";
            $row = Query("SELECT " . implode(', ', $sql) . " FROM BWS_Interactions")->fetch_array(MYSQLI_ASSOC);
            foreach ($row as $key => $value) $row[$key] = ($value == null) ? 0 : $value;

            $set = array();
            $set[] = $i + 1 . '-' . $current_year;
            foreach ($lang_ as $l => $val) $set[] = $row[$val];
            $example_data[] = $set;
        }

        $current_year += 1;
    }


    $plots['Lingue (sul numero visualizzazioni)'] = DefaultGraph($example_data, 'pie');
    end($plots)->SetDataType('text-data');

    end($plots)->DrawGraph();
    $example_data = null;
}



if (!empty($anni)) {
    // Andamento negli anni (tripla colona per le tre lingue e )
    // $data = array(array('current_year', 40, 5, 10, 3), array('Feb', 90, 8, 15, 4));

    $current_year = $start_year;

    while ($current_year <= $year) {
        $result = Query("SELECT GROUP_CONCAT(id_page) AS id_array, type FROM BWS_Pages WHERE type != 'Non definito' GROUP BY type");
        $set = array();
        $example_data = array();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_array = $row['id_array'];
            $type = $row['type'];


            if ($current_year == $year) $num = $month;
            else $num = 12;
            $set[$type] = 0;
            $data = 0;
            for ($i = 0; $i <= $num; $i++) {

                $sql = array();
                foreach ($lang_ as $l => $val) $sql[] = "SUM(JSON_EXTRACT(`$current_year`, '$[$i].$val')) AS $val";
                $row = Query("SELECT " . implode(', ', $sql) . " FROM BWS_Interactions WHERE id_page IN ($id_array)")->fetch_array(MYSQLI_ASSOC);
                foreach ($row as $key => $value) $data += $row[$key];
            }
            $set[$type] = $data;
        }

        $example_data[] = $current_year;
        foreach ($set as $s => $val) $example_data[] = $val;

        $final_data[] = $example_data;
        $current_year += 1;
    }

    // $var = Query("SELECT JSON_MERGE(`Index`, Articolo, Portale, Mix ) AS js FROM BWS_Stats WHERE year = 2022")->fetch_array(MYSQLI_ASSOC)['js'];
    // Query("UPDATE BWS_Stats SET total_pageview='$var' WHERE year = 2022");

    $plots['Andamento negli anni'] = DefaultGraph($final_data, 'stackedbars');
    end($plots)->SetLegendReverse(True);
    end($plots)->SetYDataLabelPos('plotstack');
    foreach ($set as $s => $val) $lgd[] = $s;

    end($plots)->SetLegend($lgd);
    end($plots)->SetYTitle('Tipologie negli anni');
    end($plots)->DrawGraph();
    $example_data = null;
}


// Stats

// Visitatori unici (in base al login / loading)
// Andamento rispetto al mese precedente

// $stats = array('visitatori_unici' => X, 'percentuale' => Z%)
if (!empty($stat)) {

    $stats = array();
    $current_year = 2022;

    $result = Query("SELECT CONCAT(type ,': ', COUNT(id_page)) as num FROM BWS_Pages GROUP BY type");
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) $stats['Statistiche generali'][] = $row['num'];

    $row = Query("SELECT CONCAT('Forum: ', COUNT(id_page)) as num FROM BWS_Forum")->fetch_array(MYSQLI_ASSOC);
    $stats['Statistiche generali'][] = $row['num'];

    $row = Query("SELECT CONCAT('Loading: ', loading) as l, CONCAT('Standalone: ', standalone) as s, CONCAT('Page view: ', total_pageview) as p FROM BWS_Stats WHERE year = '$year'")->fetch_array(MYSQLI_ASSOC);
    $stats['Numero'][] = $row['l'];
    $stats['Numero'][] = $row['s'];
    $stats['Numero'][] = $row['p'];
}

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

        form>div {
            display: block;
        }

        form>div>div {
            display: grid;
        }

        @media (max-width: 1000px) {

            form>div {
                display: flex;
                align-items: baseline;
                justify-content: space-evenly;
            }
        }
    </style>
</head>

<body>
    <header>
        <div>
            <h1><a href="./">Bocchio's WebSite Analytics</a></h1>
            <a href="../en/"><img src="/_img/lang/it.png" alt="Bandiera IT"></a>
        </div>
        <p style="display: block; text-align:center; margin:0"><a href=<?php echo HOST_URL; ?>><?= $i18n['subtitle'] ?></a></p>
        <hr>
    </header>
    <main>

        <form method="GET">
            <div class="card">

                <div>
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

                </div>

                <div>
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
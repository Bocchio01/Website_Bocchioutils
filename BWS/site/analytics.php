<?php

include_once "../../_setting.php";
header('Content-Type: text/html; charset=utf-8');
list($i18n, $locale, $notlocale) = LoadTranslation();
$i18n = $i18n['analytics'];

require_once '../../_lib/phplot-6.2.0/phplot.php';


function DefaultGraph($data, $type = 'linepoints')
{
    global $langArray, $width, $height;
    $plot = new PHPlot($width, $height);

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

    $plot->SetLegend($langArray);

    return $plot;
}


function graphAndData(String $query)
{

    global $langArray, $startYear, $currentYear, $currentMonth;

    $idArray = Query($query)->fetch_array(MYSQLI_ASSOC)['idArray'];

    for ($i = $startYear; $i <= $currentYear; $i++) {
        if ($i == $currentYear) $nMonth = $currentMonth;
        else $nMonth = 12;

        for ($j = 0; $j <= $nMonth; $j++) {

            $sql = array();
            foreach ($langArray as $langNumber => $langName) $sql[] = "SUM(JSON_EXTRACT(`$i`, '$[$j].$langName')) AS $langName";
            $row = Query("SELECT " . implode(', ', $sql) . " FROM BWS_Interactions WHERE id_page IN ($idArray)")->fetch_array(MYSQLI_ASSOC);
            foreach ($row as $key => $value) $row[$key] = ($value == null) ? 0 : $value;

            $set = array();
            $set[] = $j + 1 . '-' . $i;
            foreach ($langArray as $langNumber => $langName) $set[] = $row[$langName];
            $example_data[] = $set;
        }
    }

    return $example_data;
}



$currentYear = (int) date("Y");
$currentMonth = (int) date("m") - 1;

$graphAndData = array();
$graphOnly = array();
$dataOnly = array();


extract($_GET);

$langArray = empty($lingua) ? $lang : array($lingua);
$width = empty($width) ? 600 : $width;
$height = $width * 4 / 6;

$startYear = empty($startYear) ? "2022" : $startYear;


// Dati generali
$result = Query("SELECT id_page, CONCAT(name, ' - (' , type, ')') AS label FROM BWS_Pages ORDER BY type, last_modify");
while ($row = $result->fetch_array(MYSQLI_ASSOC)) $labelPages[$row['label']] = $row['id_page'];



if (!empty($stat)) {
    // Andamento generale del sito
    $result = Query("SELECT type, COUNT(id_page) as num FROM BWS_Pages GROUP BY type");
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) $dataz[$row['type']] = $row['num'];

    $row = Query("SELECT COUNT(id_page) as num FROM BWS_Forum")->fetch_array(MYSQLI_ASSOC);
    $dataz['Forum'] = $row['num'];

    $dataOnly[] = $dataz;

    $example_data = graphAndData("SELECT GROUP_CONCAT(id_page) AS idArray FROM BWS_Pages WHERE id_page != 11");
    $data['graph'] = DefaultGraph($example_data);
    $data['graph']->SetYTitle('Numero visite per mese');
    $data['graph']->DrawGraph();

    $data['title'] = 'Andamento generale del sito';

    $lastTwoMonths = array_slice($example_data, -2, 2);
    foreach ($langArray as $langNumber => $langName) {
        $data['current'][$langName] = $lastTwoMonths[1][$langNumber + 1];
        $data['prev'][$langName] = $lastTwoMonths[0][$langNumber + 1];
        $data['total'][$langName] = array_sum(array_column($example_data, $langNumber + 1));
    }

    $example_data = null;
    $graphAndData[] = $data;
}


// Andamento: Gorlu la stampante
if (!empty($id_pagina)) {

    $example_data = graphAndData("SELECT GROUP_CONCAT(id_page) AS idArray FROM BWS_Pages WHERE id_page = $id_pagina");

    $data['graph'] = DefaultGraph($example_data);
    $data['graph']->SetYTitle('Numero visite per mese');
    $data['graph']->DrawGraph();

    $data['title'] = array_search($id_pagina, $labelPages);

    $lastTwoMonths = array_slice($example_data, -2, 2);
    foreach ($langArray as $langNumber => $langName) {
        $data['current'][$langName] = $lastTwoMonths[1][$langNumber + 1];
        $data['prev'][$langName] = $lastTwoMonths[0][$langNumber + 1];
        $data['total'][$langName] = array_sum(array_column($example_data, $langNumber + 1));
    }

    $example_data = null;
    $graphAndData[] = $data;
}


// Andamento: Mix / Articoli...
if (!empty($tipo_pagina)) {

    $example_data = graphAndData("SELECT GROUP_CONCAT(id_page) AS idArray FROM BWS_Pages WHERE type = '$tipo_pagina' AND id_page != 11");

    $data['graph'] = DefaultGraph($example_data);
    $data['graph']->SetYTitle('Numero visite per mese');
    $data['graph']->DrawGraph();

    $data['title'] = $tipo_pagina;

    $lastTwoMonths = array_slice($example_data, -2, 2);
    foreach ($langArray as $langNumber => $langName) {
        $data['current'][$langName] = $lastTwoMonths[1][$langNumber + 1];
        $data['prev'][$langName] = $lastTwoMonths[0][$langNumber + 1];
        $data['total'][$langName] = array_sum(array_column($example_data, $langNumber + 1));
    }

    $example_data = null;
    $graphAndData[] = $data;
}



// Registrazioni al sito
if (!empty($registrazioni)) {
    foreach ($langArray as $langNumber => $langName) $data[$langName] = 0;

    $ser = "'" . implode("','", $langArray) . "'";

    for ($i = $startYear; $i <= $currentYear; $i++) {

        if ($i == $currentYear) $nMonth = $currentMonth;
        else $nMonth = 12;

        for ($j = 0; $j <= $nMonth; $j++) {

            $result = Query("SELECT COUNT(id_user) AS num, lang FROM BWS_Users WHERE lang IN ($ser) AND DATE_FORMAT(creation_date, '%m-%Y') = '" . sprintf('%02s-%s', $j + 1, $i) . "' GROUP BY lang ORDER BY creation_date");
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) $data[$row['lang']] += $row['num'];

            $set = array();
            $set[] = $j + 1 . '-' . $i;
            foreach ($langArray as $langNumber => $langName) $set[] = $data[$langName];
            $example_data[] = $set;
        }
    }

    $data['graph'] = DefaultGraph($example_data);
    $data['graph']->SetYTitle('Attualmente registrati');
    $data['graph']->DrawGraph();

    $data['title'] = 'Registrazioni al sito';

    $example_data = null;
    $graphOnly[] = $data;
}


// Lingue
if (!empty($lingue)) {

    $example_data = graphAndData("SELECT GROUP_CONCAT(id_page) AS idArray FROM BWS_Pages WHERE id_page != 11");

    $data['graph'] = DefaultGraph($example_data,  'pie');
    $data['graph']->SetDataType('text-data');
    $data['graph']->DrawGraph();

    $data['title'] = 'Lingue';

    $example_data = null;
    $graphOnly[] = $data;
}



if (!empty($anni)) {
    // Andamento negli anni (tripla colona per le tre lingue e )
    // $datat = array(array('currentYear', 40, 5, 10, 3), array('Feb', 90, 8, 15, 4));


    for ($i = $startYear; $i <= $currentYear; $i++) {
        $result = Query("SELECT GROUP_CONCAT(id_page) AS idArray, type FROM BWS_Pages WHERE type != 'Non definito' AND id_page != 11 GROUP BY type");
        $set = array();
        $example_data = array();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $idArray = $row['idArray'];
            $type = $row['type'];


            if ($i == $currentYear) $nMonth = $currentMonth;

            else $nMonth = 12;
            $set[$type] = 0;
            $datat = 0;
            for ($j = 0; $j <= $nMonth; $j++) {

                $sql = array();
                foreach ($langArray as $langNumber => $langName) $sql[] = "SUM(JSON_EXTRACT(`$i`, '$[$j].$langName')) AS $langName";
                $row = Query("SELECT " . implode(', ', $sql) . " FROM BWS_Interactions WHERE id_page IN ($idArray)")->fetch_array(MYSQLI_ASSOC);
                foreach ($row as $key => $value) $datat += $row[$key];
            }
            $set[$type] = $datat;
        }

        $example_data[] = $i;
        foreach ($set as $s => $langName) $example_data[] = $langName;

        $final_data[] = $example_data;
    }

    $data['graph'] = DefaultGraph($final_data, 'stackedbars');
    $data['graph']->SetLegendReverse(True);
    $data['graph']->SetYDataLabelPos('plotstack');
    foreach ($set as $s => $langName) $lgd[] = $s;
    $data['graph']->SetLegend($lgd);
    $data['graph']->SetYTitle('Tipologie negli anni');
    $data['graph']->DrawGraph();

    $data['title'] = 'Andamento negli anni';

    $example_data = null;
    $graphOnly[] = $data;
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

        td:not(:first-child) {
            text-align: center;
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

        <form method="GET">
            <div class="card">

                <div>
                    <label for="stat">
                        <input id="stat" name="stat" type="checkbox" value="1" <?php if (isset($stat)) : ?> checked <?php endif; ?>>
                        Statistiche generali
                    </label>

                    <hr>

                    <label for="registrazioni">
                        <input id="registrazioni" name="registrazioni" type="checkbox" value="1" <?php if (isset($registrazioni)) : ?> checked <?php endif; ?>>
                        Registrazioni
                    </label>

                    <hr>

                    <label for="lingue">
                        <input id="lingue" name="lingue" type="checkbox" value="1" <?php if (isset($lingue)) : ?> checked <?php endif; ?>>
                        Lingue
                    </label>

                    <hr>

                    <label for="anni">
                        <input id="anni" name="anni" type="checkbox" value="1" <?php if (isset($anni)) : ?> checked <?php endif; ?>>
                        Andamento negli anni
                    </label>

                    <hr>

                </div>

                <div>
                    <label for="id_pagina">Seleziona una pagina</label>
                    <select id="id_pagina" name="id_pagina">
                        <option></option>
                        <?php foreach ($labelPages as $name => $id_page) : ?>
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
                        <?php foreach ($lang as $langNumber) : ?>
                            <option value="<?php echo $langNumber ?>" <?php if (isset($lingua) && $langNumber == $lingua) : ?> selected="selected" <?php endif; ?>><?php echo $langNumber ?></option>
                        <?php endforeach; ?>

                    </select>

                    <hr>

                    <label for="startYear">Anno partenza</label>
                    <select id="startYear" name="startYear">
                        <option></option>
                        <?php for ($i = 2022; $i <= $currentYear; $i++) : ?>
                            <option value="<?php echo $i ?>" <?php if (isset($startYear) && $i == $startYear) : ?> selected="selected" <?php endif; ?>><?php echo $i ?></option>
                        <?php endfor; ?>

                    </select>
                </div>
            </div>

            <input type="submit" name="submit" value="Genera">
        </form>

        <hr class="spacer">



        <div class="data">

            <?php foreach ($dataOnly as $data) :
                print_r(render('./analytics/data.php', array('data' => $data)));
            endforeach; ?>

            <?php foreach ($graphAndData as $data) :
                print_r(render('./analytics/graphAndData.php', array('data' => $data)));
            endforeach; ?>

            <?php foreach ($graphOnly as $data) :
                print_r(render('./analytics/graph.php', array('data' => $data)));
            endforeach; ?>

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
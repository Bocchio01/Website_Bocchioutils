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

    if (count($data) > 12) {
        $plot->SetXLabelAngle(90);
    }

    $plot->SetLegend($langArray);

    return $plot;
}


function graphAndData(String $query)
{

    global $langArray, $byYear, $currentYear, $currentMonth;

    $idArray = Query($query)->fetch_array(MYSQLI_ASSOC)['idArray'];

    for ($i = $byYear; $i <= $currentYear; $i++) {
        if ($i == $currentYear) $nMonth = $currentMonth;
        else $nMonth = 12;

        for ($j = 0; $j < $nMonth; $j++) {

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
$currentMonth = (int) date("m");

$graphAndData = array();
$graphOnly = array();
$dataOnly = array();
$latestVisits = array();

$width = empty($width) ? 600 : $width;
$height = $width * 4 / 6;



$langArray = $lang;
$byYear = empty($_GET['fromYear']) ? 2022 : $_GET['fromYear'];;
$n = 0;

extract($_GET);
$langArray = !empty($byLang) ? array($byLang) : $lang;


$table = array();

$form = array();
$form['checkbox'] = array(
    array("id" => "isStatistics", "label" => "Statistiche generali", "value" => isset($isStatistics), "isChecked" => isset($isStatistics)),
    array("id" => "isRegistration", "label" => "Registrazioni", "value" => isset($isRegistration), "isChecked" => isset($isRegistration)),
    array("id" => "isLanguage", "label" => "Lingue", "value" => isset($isLanguage), "isChecked" => isset($isLanguage)),
    array("id" => "isYears", "label" => "Andamento negli anni", "value" => isset($isYears), "isChecked" => isset($isYears))
);

$form['select'] = array(
    "byName" => array("id" => "byName", "isMultiple" => true, "label" => "Seleziona una pagina", "hasEmptyOption" => false),
    "byType" => array("id" => "byType", "isMultiple" => true, "label" => "Seleziona una tipologia", "hasEmptyOption" => false),
    "byLang" => array("id" => "byLang", "isMultiple" => false, "label" => "Seleziona una lingua", "hasEmptyOption" => true),
    "byYear" => array("id" => "byYear", "isMultiple" => false, "label" => "Anno partenza", "hasEmptyOption" => false)
);


// Dati generali
$result = Query("SELECT p.id_page as id, p.type as type FROM BWS_Pages AS p JOIN BWS_Interactions AS i WHERE p.id_page = i.id_page AND i.last_modify >= DATE(NOW() - INTERVAL 7 DAY)");
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $latestVisits['id'][] = $row['id'];
    $latestVisits['type'][] = $row['type'];
}

$result = Query("SELECT p.id_page as id, CONCAT(p.name, ' - (' , p.type, ')') AS label FROM BWS_Pages AS p JOIN BWS_Interactions AS i WHERE p.id_page = i.id_page AND p.id_page != 11 ORDER BY i.last_modify DESC");
while ($row = $result->fetch_array(MYSQLI_ASSOC)) $form['select']['byName']['data'][] = array("optionLabel" => $row['label'], "value" => $row['id'], "isSelected" => isset($byName) ? in_array($row['id'], $byName) : false, "isLatestVisit" => $row['id'] == 11 ? in_array($row['id'], $latestVisits['id']) : false);

$result = Query("SELECT p.type as type FROM BWS_Pages AS p JOIN BWS_Interactions AS i WHERE p.id_page = i.id_page AND p.id_page != 11 GROUP BY p.type ORDER BY i.last_modify DESC");
while ($row = $result->fetch_array(MYSQLI_ASSOC)) $form['select']['byType']['data'][] = array("optionLabel" => $row['type'], "value" => $row['type'], "isSelected" => isset($byType) ? in_array($row['type'], $byType) : false, "isLatestVisit" => $row['type'] == 11 ? in_array($row['type'], $latestVisits['type']) : false);

foreach ($lang as $langNumber => $langName) $form['select']['byLang']['data'][] = array("optionLabel" => $langName, "value" => $langName, "isSelected" => (isset($byLang) && $langName == $byLang), "isLatestVisit" => null);

for ($year = 2022; $year <= $currentYear; $year++) $form['select']['byYear']['data'][] = array("optionLabel" => $year, "value" => $year, "isSelected" => (isset($byYear) && $year == $byYear), "isLatestVisit" => null);

// header('Content-Type: application/json; charset=utf-8');
// print_r($form['select'][3]);



if (!empty($isStatistics)) {
    // Andamento generale del sito
    $table[++$n] = array();
    $table[$n]['title'] = 'Statistiche generali';

    $result = Query("SELECT type, COUNT(id_page) as countOfType FROM BWS_Pages GROUP BY type");
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $table[$n]['head'][] = $row['type'];
        $table[$n]['body'][0][] = $row['countOfType'];
    };
    $table[$n]['head'][] = 'Forum';
    $table[$n]['body'][0][] = Query("SELECT COUNT(id_page) as countOfForum FROM BWS_Forum")->fetch_array(MYSQLI_ASSOC)['countOfForum'];
    // header('Content-Type: application/json; charset=utf-8');
    // print_r($table[$n]);

    // $dataOnly[] = $dataz;

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
if (!empty($byName)) {
    foreach ($byName as $number => $id) {
        $example_data = graphAndData("SELECT GROUP_CONCAT(id_page) AS idArray FROM BWS_Pages WHERE id_page = $id");

        $data['graph'] = DefaultGraph($example_data);
        $data['graph']->SetYTitle('Numero visite per mese');
        $data['graph']->DrawGraph();

        $data['title'] = $form['select']['byName']['data'][array_search($id, array_column($form['select']['byName']['data'], 'value'))]['optionLabel'];

        $lastTwoMonths = array_slice($example_data, -2, 2);
        foreach ($langArray as $langNumber => $langName) {
            $data['current'][$langName] = $lastTwoMonths[1][$langNumber + 1];
            $data['prev'][$langName] = $lastTwoMonths[0][$langNumber + 1];
            $data['total'][$langName] = array_sum(array_column($example_data, $langNumber + 1));
        }

        $example_data = null;
        $graphAndData[] = $data;
    }
}


// Andamento: Mix / Articoli...
if (!empty($byType)) {
    foreach ($byType as $number => $tipo) {

        $example_data = graphAndData("SELECT GROUP_CONCAT(id_page) AS idArray FROM BWS_Pages WHERE type = '$tipo' AND id_page != 11");

        $data['graph'] = DefaultGraph($example_data);
        $data['graph']->SetYTitle('Numero visite per mese');
        $data['graph']->DrawGraph();

        $data['title'] = $tipo;

        $lastTwoMonths = array_slice($example_data, -2, 2);
        foreach ($langArray as $langNumber => $langName) {
            $data['current'][$langName] = $lastTwoMonths[1][$langNumber + 1];
            $data['prev'][$langName] = $lastTwoMonths[0][$langNumber + 1];
            $data['total'][$langName] = array_sum(array_column($example_data, $langNumber + 1));
        }

        $example_data = null;
        $graphAndData[] = $data;
    }
}



// Registrazioni al sito
if (!empty($isRegistration)) {
    foreach ($langArray as $langNumber => $langName) $data[$langName] = 0;

    $ser = "'" . implode("','", $langArray) . "'";

    for ($i = $byYear; $i <= $currentYear; $i++) {

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
if (!empty($isLanguage)) {

    $example_data = graphAndData("SELECT GROUP_CONCAT(id_page) AS idArray FROM BWS_Pages WHERE id_page != 11");

    $data['graph'] = DefaultGraph($example_data,  'pie');
    $data['graph']->SetDataType('text-data');
    $data['graph']->DrawGraph();

    $data['title'] = 'Lingue';

    $example_data = null;
    $graphOnly[] = $data;
}



if (!empty($isYears)) {
    // Andamento negli anni (tripla colona per le tre isLanguage e )
    // $datat = array(array('currentYear', 40, 5, 10, 3), array('Feb', 90, 8, 15, 4));


    for ($i = $byYear; $i <= $currentYear; $i++) {
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
                    <?php foreach ($form['checkbox'] as $elName => $elValue) : ?>
                        <label for="<?php echo $elValue['id'] ?>">
                            <input id="<?php echo $elValue['id'] ?>" name="<?php echo $elValue['id'] ?>" type="checkbox" value="1" <?php echo $elValue['isChecked'] ? "checked" : "" ?>>
                            <?php echo $elValue['label'] ?>
                        </label>
                    <?php endforeach; ?>

                    <?php foreach ($form['select'] as $elName => $elValue) : ?>
                        <label for="<?php echo $elValue['id'] ?>"><?php echo $elValue['label'] ?></label>
                        <select id="<?php echo $elValue['id'] ?>" name="<?php echo $elValue['isMultiple'] ? $elValue['id'] . "[]" : $elValue['id'] ?>" <?php echo $elValue['isMultiple'] ? "multiple" : "" ?>>
                            <?php if ($elValue['hasEmptyOption']) : ?> <option></option><?php endif; ?>
                            <?php foreach ($elValue['data'] as $dataName => $dataValue) : ?>
                                <option value="<?php echo $dataValue['value'] ?>" style="<?php echo $dataValue['isLatestVisit'] ? "font-weight:bold;" : "" ?>" <?php echo $dataValue['isSelected'] ? "selected" : "" ?>><?php echo $dataValue['optionLabel'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endforeach; ?>
                </div>
            </div>

            <input type="submit" name="submit" value="Genera">

        </form>

        <hr class="spacer">



        <div class="data">

            <?php foreach ($table as $data) :
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
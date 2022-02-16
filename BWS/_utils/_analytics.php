<?php

include_once "../../setting.php";

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

$color_list = array(
    'SkyBlue',
    'green',
    'orange',
    'blue',
    'red',
    'DarkGreen',
    'purple',
    'peru',
    'cyan',
    'salmon',
    'SlateBlue',
    'YellowGreen',
    'magenta',
    'aquamarine1',
    'gold',
    'violet'
);

$year = (int) date("Y");


$month = (int) date("m") - 1;

$list_of_date = array();
$plots = array();
extract($_GET);

if (!empty($lingua)) {
    $lang_ = array($lingua);
} else {
    $lang_ = $lang;
}

if (!empty($anno_partenza)) {
    $start_year = $anno_partenza;
} else {
    $start_year = "2022";
}

$first_point = array();
$first_point[] = 'Start';
foreach ($lang_ as $l => $val) $first_point[] = 0;

// Dati generali
$result = Query("SELECT id_page, CONCAT(name, ' - (' , type, ')') AS descr FROM BWS_Pages ORDER BY type");
while ($row = $result->fetch_array(MYSQLI_ASSOC)) $data_pages[$row['descr']] = $row['id_page'];


if (!empty($registrazioni)) {
    // Registrazioni al sito
    foreach ($lang_ as $l => $val) $data[$val] = 0;

    $example_data[] = $first_point;

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



if (!empty($id_pagina)) {
    // Andamento: Gorlu la stampante
    $example_data[] = $first_point;

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



if (!empty($tipo_pagina)) {
    // Andamento: Mix / Articoli...
    $id_array = Query("SELECT GROUP_CONCAT(id_page) AS id_array FROM BWS_Pages WHERE type = '$tipo_pagina'")->fetch_array(MYSQLI_ASSOC)['id_array'];

    $example_data[] = $first_point;

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



if (!empty($lingue)) {
    // Lingue
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


    // $example_data[] = $first_point;
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

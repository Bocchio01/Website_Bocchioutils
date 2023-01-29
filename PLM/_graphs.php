<?php

require_once '../_lib/phplot-6.2.0/phplot.php';

function DefaultGraph($data, $type = 'linepoints')
{
    // print_r($data);
    $plot = new PHPlot(500, 350);
    // print_r($data);
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

    // $plot->SetXLabelType('date');
    $plot->SetXTickLabelPos('none');
    $plot->SetXTickPos('none');
    $plot->SetLineWidths(3);
    // $plot->SetYTickIncrement(1);

    if (count($data) >= 5) {
        $plot->SetXLabelAngle(90);
    }

    return $plot;
}



function getPaymentsDistribution(string $type, int $id): array
{
    $data = array();
    $title = array('en' => 'Payments distribution', 'it' => 'Distribuzione dei pagamenti');
    $counts = array();

    $result = Query("SELECT
    ROUND(SUM(L.price) + SUM(L.extra), 2) as total_price,
    CONCAT(A.name, ' ', A.surname) as name
    FROM PLM_Lessons_List as L
    JOIN PLM_Alumni as A ON L.id_alumno = A.id_alumno
    WHERE $type = $id
    GROUP BY L.id_alumno
    ORDER BY total_price DESC");

    if ($result->num_rows) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $data[] = array($row['name'], $row['total_price']);
        }
    }

    $graph = DefaultGraph($data, 'pie');
    $graph->SetDataType('text-data-single');
    foreach ($data as $subject) $graph->SetLegend(implode(': ', $subject));

    $graph->DrawGraph();

    return array(
        'title' => $title,
        'type' => 'graph',
        'table' => 'PLM_Lessons_List',
        'url' => $graph->EncodeImage(),
    );
}


function getSubjectGraph(string $type, int $id): array
{
    $data = array();
    $title = array('en' => 'Subjects', 'it' => 'Materie');
    $counts = array();

    $result = Query("SELECT
    subject
    FROM PLM_Lessons_List
    WHERE $type = $id");

    if ($result->num_rows) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $array = json_decode($row['subject'], true);
            $counts = array_merge($counts, $array);
        }
    }

    foreach (array_count_values($counts) as $key => $value) {
        $data[] = array($key, $value);
    }

    $graph = DefaultGraph($data, 'pie');
    $graph->SetDataType('text-data-single');
    foreach ($data as $subject) $graph->SetLegend(implode(': ', $subject));

    $graph->DrawGraph();

    return array(
        'title' => $title,
        'type' => 'graph',
        'table' => 'PLM_Lessons_List',
        'url' => $graph->EncodeImage(),
    );
}


function getMonthlyHoursGraph(string $type, int $id): array
{
    $data = array();
    $title = array('en' => 'Monthly Hours for Subject', 'it' => 'Frequenza materie svolte');

    $result = Query("SELECT
    DATE_FORMAT(date_lessons, '%M-%Y') AS date,
    ROUND(SUM(minutes) / 60, 2) as hours
    FROM PLM_Lessons_List
    WHERE $type = $id
    GROUP BY MONTH(date_lessons), YEAR(date_lessons)
    ORDER BY date_lessons ASC");

    if ($result->num_rows) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $data[] = $row;
        }
    }

    $graph = DefaultGraph($data, 'stackedbars');
    $graph->DrawGraph();

    return array(
        'title' => $title,
        'type' => 'graph',
        'table' => 'PLM_Lessons_List',
        'url' => $graph->EncodeImage()
    );
}

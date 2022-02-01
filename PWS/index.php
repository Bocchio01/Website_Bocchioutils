<?php

include_once "../setting.php";

header('Content-Type: text/html; charset=utf-8');
require_once '../_lib/phplot-6.2.0/phplot.php';


function DefaultGraph($data, $type = 'linepoints')
{
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


$list_of_date = array();
$plots = array();

// Dati generali
$result = Query("SELECT id_page, name, type FROM PWS_Pages ORDER BY type");
$data_pages = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

    $data_pages[$row['name'] . ' - (' . $row['type'] . ')'] = $row['id_page'];
}

extract($_GET);

if (!empty($registrazioni)) {
    // Registrazioni al sito
    $result = Query("SELECT COUNT(id_user), DATE_FORMAT(creation_date,'%m_%Y') AS date, lang FROM PWS_Users GROUP BY DATE_FORMAT(creation_date, '%m_%Y'), lang ORDER BY creation_date");

    $example_data = array();

    foreach ($lang as $l) $prev[$l] = 0;

    $date = 'Start';
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

        if ($date != $row["date"]) {
            $example_data[] = array($date, $prev[$lang[0]], $prev[$lang[1]], $prev[$lang[2]]);
            $date = $row["date"];
        }

        $prev[$row['lang']] += $row['COUNT(id_user)'];
        $date = $row["date"];
    }

    $example_data[] = array($date, $prev[$lang[0]], $prev[$lang[1]], $prev[$lang[2]]);

    // $legend = array();
    // foreach ($example_data as $row) end($plots)->SetLegend(implode(': ', $row));


    $plots['Registrazioni al sito'] = DefaultGraph($example_data);

    foreach ($lang as $l) end($plots)->SetLegend($l . ': ' . $prev[$l]);
    end($plots)->SetYTitle('Attualmente registrati');
    end($plots)->DrawGraph();
}



if (!empty($id_pagina)) {
    // Andamento: Gorlu la stampante
    $result = Query("SELECT * FROM PWS_Interactions WHERE id_page = $id_pagina");
    $fields = Query("DESC PWS_Interactions");

    $row = $result->fetch_array((MYSQLI_ASSOC));
    $example_data = array();
    $example_data[] = array('Start', 0, 0, 0);

    while ($field = $fields->fetch_array(MYSQLI_ASSOC)['Field']) {
        if ($field != 'id' && $field != 'id_page') {
            $data = json_decode($row[$field]);
            $example_data[] = array($field, $data->{$lang[0]}, $data->{$lang[1]}, $data->{$lang[2]});
        }
    }

    $plots['Andamento: ' . array_search($id_pagina, $data_pages)] = DefaultGraph($example_data);
    end($plots)->SetYTitle('Numero visite per mese');
    end($plots)->DrawGraph();
}


if (!empty($tipo_pagina)) {
    // Andamento: Mix / Articoli...
    $result = Query("SELECT id_page FROM PWS_Pages WHERE type = '$tipo_pagina'");
    $fields = Query("DESC PWS_Interactions");

    $id = '';
    while ($row = $result->fetch_array((MYSQLI_ASSOC))) {
        $id .= $row['id_page'] . ',';
    }
    $id = substr($id, 0, -1);

    $result = Query("SELECT * FROM PWS_Interactions WHERE id_page IN ($id)");


    $row = $result->fetch_array((MYSQLI_ASSOC));
    $example_data = array();
    $example_data[] = array('Start', 0, 0, 0);

    while ($field = $fields->fetch_array(MYSQLI_ASSOC)['Field']) {
        if ($field != 'id' && $field != 'id_page') {
            $result = Query("SELECT $field FROM PWS_Interactions WHERE id_page IN ($id)");
            $data = new stdClass();
            $data->{$lang[0]} = 0;
            $data->{$lang[1]} = 0;
            $data->{$lang[2]} = 0;
            while ($row = $result->fetch_array(MYSQLI_ASSOC)[$field]) {
                $int = json_decode($row);
                $data->{$lang[0]} += $int->{$lang[0]};
                $data->{$lang[1]} += $int->{$lang[1]};
                $data->{$lang[2]} += $int->{$lang[2]};
            }
            $example_data[] = array($field, $data->{$lang[0]}, $data->{$lang[1]}, $data->{$lang[2]});
        }
    }

    $plots['Andamento: ' . $tipo_pagina] = DefaultGraph($example_data);
    end($plots)->SetYTitle('Numero visite per mese');
    end($plots)->DrawGraph();
}



if (!empty($lingue)) {
    // Lingue
    $result = Query("SELECT * FROM PWS_Interactions");
    $fields = Query("DESC PWS_Interactions");

    $row = $result->fetch_array((MYSQLI_ASSOC));
    $example_data = array();
    $example_data[] = array('Start', 0, 0, 0);

    $data = new stdClass();
    $data->{$lang[0]} = 0;
    $data->{$lang[1]} = 0;
    $data->{$lang[2]} = 0;

    while ($field = $fields->fetch_array(MYSQLI_ASSOC)['Field']) {
        if ($field != 'id' && $field != 'id_page') {
            $result = Query("SELECT $field FROM PWS_Interactions");
            while ($row = $result->fetch_array(MYSQLI_ASSOC)[$field]) {
                $int = json_decode($row);
                $data->{$lang[0]} += $int->{$lang[0]};
                $data->{$lang[1]} += $int->{$lang[1]};
                $data->{$lang[2]} += $int->{$lang[2]};
            }
        }
    }

    $data = array(array($lang[0], $data->{$lang[0]}), array($lang[1], $data->{$lang[1]}), array($lang[2], $data->{$lang[2]}));

    $plots['Lingue (sul numero visualizzazioni)'] = DefaultGraph($data, 'pie');
    end($plots)->SetDataType('text-data-single');

    foreach ($data as $row) end($plots)->SetLegend(implode(': ', $row));

    end($plots)->DrawGraph();
}




// Stats

// Visitatori unici (in base al login)
// Andamento rispetto al mese precedente

// $stats = array('visitatori_unici' => X, 'percentuale' => Z%)




?>

<!DOCTYPE HTML>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="author" content="Tommaso Bocchietti">
    <meta name="robots" content="noindex">
    <title>Bocchio's WebSite Analytics</title>
    <style>
        @import url("../style.css");
    </style>
</head>

<body>
    <header>
        <h1>Bocchio's WebSite Analytics</h1>
        <hr>
    </header>
    <main>

        <form method="GET">
            <div class="card">

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

                <!-- <hr>

                <label for="lingua">Seleziona una lingua</label>
                <select id="lingua" name="lingua">
                    <option></option>
                    <?php foreach ($lang as $l) : ?>
                        <option value="<?php echo $l ?>" <?php if (isset($lingua) && $l == $lingua) : ?> selected="selected" <?php endif; ?>><?php echo $l ?></option>
                    <?php endforeach; ?>

                </select> -->

            </div>

            <input type="submit" name="submit" value="Genera">
        </form>

        <hr class="spacer">

        <div class="data">

            <div class="card number">
                <div>
                    <h2>Legenda colori</h2>
                    <?php foreach ($lang as $l => $value) : ?>
                        <div style="background-color: <?php echo $color_list[$l] ?>"><?php echo $value ?></div>
                    <?php endforeach; ?>
                </div>

                <hr class="spacer">

                <div>
                    <h2>Legenda colori 2</h2>
                    <?php foreach ($lang as $l => $value) : ?>
                        <div style="background-color: <?php echo $color_list[$l] ?>"><?php echo $value ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

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
    document.getElementById('copyright').innerText = "Copyright Tommaso Bocchietti @ " + new Date().getFullYear();
</script>

</html>
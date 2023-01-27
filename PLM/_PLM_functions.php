<?php

require_once '../_lib/phplot-6.2.0/phplot.php';

function checkAuthorization()
{
    $token = '';
    $PLM_token = '';
    if (!empty($_COOKIE['token'])) $token = $_COOKIE['token'];
    if (!empty($_COOKIE['PLM_token'])) $PLM_token = $_COOKIE['PLM_token'];

    if (empty($token) && empty($PLM_token)) {
        die(returndata(1, 'Not authorized.'));
    }

    $result = Query("SELECT P.*, U.token as BWS_token
    FROM PLM_Professor as P
    JOIN BWS_Users as U ON U.id_user = P.id_professor
    WHERE U.token = '$token' OR P.token = '$PLM_token'");

    if ($result->num_rows == 1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if ($row['approved'] == 1) {

            setcookie('token', $row['BWS_token'], time() + 3600 * 24 * 30, '/');
            setcookie('PLM_token', $row['token'], time() + 3600 * 24 * 30, '/');

            return $row['id'];
        } else {
            die(returndata(1, 'Professore non approvato.'));
        }
    } else {
        die(returndata(1, 'Professore non riconosciuto.'));
    }
}


function calculatePrice($minutes, $price_per_hour)
{
    $nSteps = 2;
    $price_per_step = $price_per_hour / $nSteps;

    $price = ((int) (($minutes + 60 / $nSteps / 2) / 60 * $nSteps)) * $price_per_step;

    return $price;
}


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

    if (count($data) >= 12) {
        $plot->SetXLabelAngle(90);
    }

    return $plot;
}


function getStats(string $type, int $id): array
{
    $stats = array();
    $title = array('en' => 'Overall Stats', 'it' => 'Statistiche Complessive');
    // $baseTab = array('date_lessons', 'arguments', 'minutes', 'total_price');
    // $sudoTab = array('');
    $locale = array(
        'en' => array(
            'year' => 'Year',
            'total_hours' => 'Hours',
            'total_amount' => 'Paid',
            'total_price' => 'Price',
            'difference' => 'Difference'
        ),
        'it' => array(
            'year' => 'Anno',
            'total_hours' => 'Ore',
            'total_amount' => 'Pagato',
            'total_price' => 'Prezzo',
            'difference' => 'Differenza'
        ),
    );

    $tot_amount = 0;
    $tot_price = 0;
    $tot_hours = 0;

    $paymentsList = Query("SELECT
        ROUND(SUM(amount), 2) as total_amount
        FROM PLM_Lessons_Payments
        WHERE $type = $id
        GROUP BY YEAR(date_received)");

    $lessonsList = Query("SELECT
        ROUND(SUM(price) + SUM(extra), 2) as total_price,
        ROUND(SUM(minutes) / 60, 2) as total_hours,
        YEAR(date_lessons) as year
        FROM PLM_Lessons_List
        WHERE $type = $id
        GROUP BY YEAR(date_lessons)");

    // $paymentsList = Query("SELECT
    // ROUND(SUM(amount), 2) as total_amount
    // FROM PLM_Lessons_Payments
    // WHERE $type = $id
    // GROUP BY YEAR(date_received)");

    // $lessonsList = Query("SELECT
    // ROUND(SUM(P.amount), 2) as total_amount,
    // ROUND(SUM(L.price) + SUM(L.extra), 2) as total_price,
    // ROUND(SUM(L.minutes) / 60, 2) as total_hours,
    // YEAR(L.date_lessons) as year
    // FROM PLM_Lessons_List as L, PLM_Lessons_Payments as P
    // WHERE L.$type = $id
    // AND P.$type = $id
    // AND YEAR(P.date_received) = YEAR(L.date_lessons)
    // GROUP BY YEAR(L.date_lessons)");


    for ($i = 0; $i < $lessonsList->num_rows; $i++) {
        $paymentsList->data_seek($i);
        $lessonsList->data_seek($i);
        $rowPayment = $paymentsList->fetch_assoc();
        $rowLesson = $lessonsList->fetch_assoc();

        $tot_hours += $rowLesson['total_hours'];
        $tot_amount += $rowPayment['total_amount'];
        $tot_price += $rowLesson['total_price'];


        $stats[] = array(
            'year' => $rowLesson['year'],
            'total_hours' => $rowLesson['total_hours'],
            'total_amount' => $rowPayment['total_amount'],
            'total_price' => $rowLesson['total_price'],
            'difference' => $rowPayment['total_amount'] - $rowLesson['total_price'],
        );
    }

    $stats[] = array(
        'year' => 'Tot',
        'total_hours' => $tot_hours,
        'total_amount' => $tot_amount,
        'total_price' => $tot_price,
        'difference' => $tot_amount - $tot_price,
    );

    return array(
        'title' => $title,
        'type' => 'table',
        'table' => '',
        'locale' => $locale,
        // 'baseTab' => $baseTab,
        // 'sudoTab' => $sudoTab,
        'data' => $stats
    );
}

function getPendingPayments(string $type, int $id): array
{
    $stats = array();
    $title = array('en' => 'Pending Payments', 'it' => 'Pagamenti in attesa');
    $locale = array(
        'en' => array(
            'name' => 'Alumno',
            'total_amount' => 'Paid',
            'total_price' => 'Price',
            'difference' => 'Difference',
        ),
        'it' => array(
            'name' => 'Alunno',
            'total_amount' => 'Pagato',
            'total_price' => 'Prezzo',
            'difference' => 'Differenza',
        ),
    );


    // $result = Query("SELECT
    //     A.id_alumno,
    //     ROUND(SUM(amount), 2) as total_amount,
    //     ROUND(SUM(price) + SUM(extra), 2) as total_price
    //     FROM PLM_Alumni as A
    //     JOIN PLM_Lessons_List as L on L.id_alumno = A.id_alumno
    //     JOIN PLM_Lessons_Payments as P on P.id_alumno = A.id_alumno
    //     WHERE A.$type = $id
    //     GROUP BY A.id_alumno");

    $result = Query("SELECT id_alumno, CONCAT(name, ' ', surname) as name
    FROM PLM_Alumni
    WHERE JSON_CONTAINS(id_auth_professors, $id, '$')
    OR id_owner = $id");

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

            $payment = Query("SELECT
            ROUND(SUM(amount), 2) as total_amount
            FROM PLM_Lessons_Payments
            WHERE id_alumno = $row[id_alumno]")->fetch_array(MYSQLI_ASSOC);

            $lessons = Query("SELECT
            ROUND(SUM(price) + SUM(extra), 2) as total_price
            FROM PLM_Lessons_List
            WHERE id_alumno = $row[id_alumno]")->fetch_array(MYSQLI_ASSOC);

            $stats[] = array(
                'name' => $row['name'],
                'total_amount' => $payment['total_amount'],
                'total_price' => $lessons['total_price'],
                'difference' => $payment['total_amount'] - $lessons['total_price'],
            );
        }
    }

    return array(
        'title' => $title,
        'type' => 'table',
        'table' => '',
        'locale' => $locale,
        // 'baseTab' => $baseTab,
        // 'sudoTab' => $sudoTab,
        'data' => $stats
    );
}

function getLessons(string $type, int $id): array
{
    $lessonsList = array();
    $title = array('en' => 'Lessons', 'it' => 'Lezioni');
    $baseTab = array('date_lessons', 'arguments', 'minutes', 'total_price');
    $sudoTab = array('id', 'date_lessons', 'subject', 'arguments', 'minutes', 'price', 'extra');
    $controls = array('modify', 'delete');
    $locale = array(
        'en' => array(
            'id' => 'ID',
            'id_alumno' => 'ID Alumno',
            'id_professor' => 'ID Professor',
            'price' => 'Price',
            'extra' => 'Extra',
            'subject' => 'Subjects',
            'date_lessons' => 'Date',
            'arguments' => 'Arguments',
            'minutes' => 'Minutes',
            'total_price' => 'Price',
            'modify' => 'Modify',
            'delete' => 'Delete'
        ),
        'it' => array(
            'id' => 'ID',
            'id_alumno' => 'ID Alumno',
            'id_professor' => 'ID Professore',
            'price' => 'Prezzo',
            'extra' => 'Extra',
            'subject' => 'Materie',
            'date_lessons' => 'Data',
            'arguments' => 'Argomenti',
            'minutes' => 'Minuti',
            'total_price' => 'Prezzo',
            'modify' => 'Modifica',
            'delete' => 'Elimina'
        ),
    );

    $result = Query("SELECT
     id,
     id_alumno,
     id_professor,
     date_lessons,
     minutes,
     price,
     extra,
     subject,
     arguments,
     ROUND(price + extra, 2) as total_price
     FROM PLM_Lessons_List
     WHERE $type = $id
     ORDER BY date_lessons DESC
     LIMIT 20");


    if ($result->num_rows) while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $lessonsList[] = $row;
    }

    return array(
        'title' => $title,
        'type' => 'table',
        'table' => 'PLM_Lessons_List',
        'locale' => $locale,
        'baseTab' => $baseTab,
        'sudoTab' => $sudoTab,
        'controls' => $controls,
        'data' => $lessonsList
    );
}


function getPayments(string $type, int $id): array
{
    $paymentsList = array();
    $title = array('en' => 'Payments', 'it' => 'Pagamenti');
    $baseTab = array('date_received', 'location', 'type', 'amount');
    $sudoTab = array('id', 'date_received', 'location', 'type', 'amount');
    $controls = array('modify', 'delete');
    $locale = array(
        'en' => array(
            'id' => 'ID',
            'id_alumno' => 'ID Alumno',
            'id_professor' => 'ID Professor',
            'date_received' => 'Date received',
            'last_modify' => 'Last modify',
            'location' => 'Location',
            'type' => 'Type',
            'amount' => 'Payed',
            'modify' => 'Modify',
            'delete' => 'Delete'
        ),
        'it' => array(
            'id' => 'ID',
            'id_alumno' => 'ID Alumno',
            'id_professor' => 'ID Professore',
            'date_received' => 'Data ricezione',
            'last_modify' => 'Ultima modifica',
            'location' => 'Luogo',
            'type' => 'Tipologia',
            'amount' => 'Ammontare',
            'modify' => 'Modifica',
            'delete' => 'Elimina'
        )
    );

    $result = Query("SELECT
     id,
     id_alumno,
     id_professor,
     ROUND(amount, 2) as amount,
     date_received,
     last_modify,
     type,
     location
     FROM PLM_Lessons_Payments
     WHERE $type = $id
     ORDER BY date_received DESC
     LIMIT 20");


    if ($result->num_rows) while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $paymentsList[] = $row;
    }
    return array(
        'title' => $title,
        'type' => 'table',
        'table' => 'PLM_Lessons_Payments',
        'locale' => $locale,
        'baseTab' => $baseTab,
        'sudoTab' => $sudoTab,
        'controls' => $controls,
        'data' => $paymentsList
    );
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

function getAlumniList(int $id_professor): array
{
    $alumniList = array();
    $title = array('en' => 'Alumni data', 'it' => 'Dati alumni');
    $baseTab = array('name', 'surname', 'email', 'default_price', 'default_extra');
    $sudoTab = array('owner', 'name', 'surname', 'email', 'default_price', 'default_extra', 'entry_password');
    $controls = array('modify', 'delete');
    $locale = array(
        'en' => array(
            'id' => 'ID',
            'id_owner' => 'ID_Owner',
            'id_auth_professors' => 'ID_Professors',
            'owner' => 'Owner',
            'auth_professors' => 'Authorized professors',
            'name' => 'Name',
            'surname' => 'Surname',
            'email' => 'Email',
            'default_price' => 'Price per hour',
            'default_extra' => 'Extra',
            'entry_password' => 'Password',
            'modify' => 'Modify',
            'delete' => 'Delete'
        ),
        'it' => array(
            'id' => 'ID',
            'id_owner' => 'ID_Proprietario',
            'id_auth_professors' => 'ID_Professori',
            'owner' => 'Proprietario',
            'auth_professors' => 'Professori autorizzati',
            'name' => 'Nome',
            'surname' => 'Cognome',
            'email' => 'Email',
            'default_price' => 'Prezzo orario',
            'default_extra' => 'Extra',
            'entry_password' => 'Password',
            'modify' => 'Modifica',
            'delete' => 'Elimina'
        ),
    );



    $result = Query("SELECT A.*, A.id_alumno as id, U.nickname as owner
    FROM PLM_Alumni as A
    JOIN PLM_Professor as P ON A.id_owner = P.id
    JOIN BWS_Users as U ON P.id_professor = U.id_user
    WHERE JSON_CONTAINS(id_auth_professors, $id_professor, '$')
    OR id_owner = $id_professor");

    if ($result->num_rows) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $alumniList[] = $row;
        }
    }

    return array(
        'title' => $title,
        'type' => 'table',
        'table' => 'PLM_Alumni',
        'locale' => $locale,
        'baseTab' => $baseTab,
        'sudoTab' => $sudoTab,
        'controls' => $controls,
        'data' => $alumniList
    );
}

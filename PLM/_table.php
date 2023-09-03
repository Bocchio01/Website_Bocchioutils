<?php


function getStats(string $type, int $id): array
{
    $stats = array();
    $title = array('en' => 'Overall Stats', 'it' => 'Statistiche Complessive');
    // $baseTab = array('date_lessons', 'arguments', 'minutes', 'total_price');
    // $sudoTab = array('');
    $locale = array(
        'en' => array(
            'year' => 'Year',
            'total_hours' => 'Hours [h:m]',
            'total_amount' => 'Paid [€]',
            'total_price' => 'Price [€]',
            'difference' => 'Difference [€]'
        ),
        'it' => array(
            'year' => 'Anno',
            'total_hours' => 'Ore [h:m]',
            'total_amount' => 'Pagato [€]',
            'total_price' => 'Prezzo [€]',
            'difference' => 'Differenza [€]'
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
        ROUND(SUM(minutes), 2) as total_hours,
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

    foreach ($stats as $key => $row) {
        $stats[$key]['total_hours'] = timeLength($row['total_hours']);
        $stats[$key]['total_amount'] = number_format($row['total_amount'], 2, '.', '');
        $stats[$key]['total_price'] = number_format($row['total_price'], 2, '.', '');
        $stats[$key]['difference'] = number_format($row['difference'], 2, '.', '');
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

function getPendingPayments(string $type, int $id): array
{
    // Fix the case that the student has more auth_professor and calculare the pending payments for each one
    $stats = array();
    $title = array('en' => 'Pending Payments', 'it' => 'Pagamenti in attesa');
    $locale = array(
        'en' => array(
            'name' => 'Alumno',
            'total_amount' => 'Paid [€]',
            'total_price' => 'Price [€]',
            'difference' => 'Difference [€]',
        ),
        'it' => array(
            'name' => 'Alunno',
            'total_amount' => 'Pagato [€]',
            'total_price' => 'Prezzo [€]',
            'difference' => 'Differenza [€]',
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
    WHERE JSON_CONTAINS(id_auth_professors, '$id', '$')");

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

    foreach ($stats as $key => $row) {
        $stats[$key]['total_amount'] = number_format($row['total_amount'], 2, '.', '');
        $stats[$key]['total_price'] = number_format($row['total_price'], 2, '.', '');
        $stats[$key]['difference'] = number_format($row['difference'], 2, '.', '');
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

function getLessons(string $type, int $id, int $nRow): array
{
    $lessonsList = array();
    $title = array('en' => 'Lessons', 'it' => 'Lezioni');
    $baseTab = array('date_lessons', 'arguments', 'minutes', 'total_price');
    $sudoTab = array('name', 'date_lessons', 'subject', 'arguments', 'minutes', 'price', 'extra');
    $controls = array('modify', 'delete');
    $locale = array(
        'en' => array(
            'id' => 'ID',
            'id_alumno' => 'ID Alumno',
            'id_professor' => 'ID Professor',
            'name' => 'Alumno',
            'price' => 'Price [€]',
            'extra' => 'Extra [€]',
            'subject' => 'Subjects',
            'date_lessons' => 'Date',
            'arguments' => 'Arguments',
            'minutes' => 'Minutes [m]',
            'total_price' => 'Price [€]',
            'modify' => 'Modify',
            'delete' => 'Delete'
        ),
        'it' => array(
            'id' => 'ID',
            'id_alumno' => 'ID Alumno',
            'id_professor' => 'ID Professore',
            'name' => 'Alumno',
            'price' => 'Prezzo [€]',
            'extra' => 'Extra [€]',
            'subject' => 'Materie',
            'date_lessons' => 'Data',
            'arguments' => 'Argomenti',
            'minutes' => 'Minuti [m]',
            'total_price' => 'Prezzo [€]',
            'modify' => 'Modifica',
            'delete' => 'Elimina'
        ),
    );

    $result = Query("SELECT
     CONCAT(A.name, ' ', A.surname) as name,
     P.id,
     P.id_alumno,
     P.id_professor,
     P.date_lessons,
     P.minutes,
     P.price,
     P.extra,
     P.subject,
     P.arguments,
     ROUND(P.price + P.extra, 2) as total_price
     FROM PLM_Lessons_List as P
     JOIN PLM_Alumni as A on A.id_alumno = P.id_alumno
     WHERE P.$type = $id
     ORDER BY P.date_lessons DESC
     LIMIT $nRow");


    if ($result->num_rows) while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $row['subject'] = json_decode($row['subject'], true);
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


function getPayments(string $type, int $id, int $nRow): array
{
    $paymentsList = array();
    $title = array('en' => 'Payments', 'it' => 'Pagamenti');
    $baseTab = array('date_received', 'location', 'type', 'amount');
    $sudoTab = array('name', 'date_received', 'location', 'type', 'amount');
    $controls = array('modify', 'delete');
    $locale = array(
        'en' => array(
            'id' => 'ID',
            'id_alumno' => 'ID Alumno',
            'id_professor' => 'ID Professor',
            'name' => 'Alumno',
            'date_received' => 'Date received',
            'last_modify' => 'Last modify',
            'location' => 'Location',
            'type' => 'Type',
            'amount' => 'Payed [€]',
            'modify' => 'Modify',
            'delete' => 'Delete'
        ),
        'it' => array(
            'id' => 'ID',
            'id_alumno' => 'ID Alumno',
            'id_professor' => 'ID Professore',
            'name' => 'Alumno',
            'date_received' => 'Data ricezione',
            'last_modify' => 'Ultima modifica',
            'location' => 'Luogo',
            'type' => 'Tipologia',
            'amount' => 'Ammontare [€]',
            'modify' => 'Modifica',
            'delete' => 'Elimina'
        )
    );

    $result = Query("SELECT
     P.id,
     P.id_alumno,
     P.id_professor,
     ROUND(P.amount, 2) as amount,
     P.date_received,
     P.last_modify,
     P.type,
     P.location,
     CONCAT(A.name, ' ', A.surname) as name
     FROM PLM_Lessons_Payments as P
     JOIN PLM_Alumni as A on A.id_alumno = P.id_alumno
     WHERE P.$type = $id
     ORDER BY P.date_received DESC
     LIMIT $nRow");


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



function getProfessorList(): array
{
    $professorList = array();
    $title = array('en' => 'Professor list', 'it' => 'Elenco professori');
    $locale = array(
        'en' => array(),
        'it' => array()
    );

    $result = Query("SELECT id, CONCAT(name, ' ', surname) as nickname
    FROM PLM_Professor
    WHERE approved = 1");

    if ($result->num_rows) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $professorList[] = $row;
        }
    }

    return array(
        'title' => $title,
        'type' => 'table',
        'table' => 'PLM_Professor',
        'locale' => $locale,
        'data' => $professorList
    );
}



function getAlumniList(int $id_professor): array
{
    $alumniList = array();
    $title = array('en' => 'Alumni data', 'it' => 'Dati alumni');
    $baseTab = array('name', 'surname', 'email', 'default_subjects', 'default_price', 'default_extra');
    $sudoTab = array('owner', 'name', 'surname', 'email', 'default_subjects', 'default_price', 'default_extra', 'entry_password');
    $controls = array('modify');
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
            'default_subjects' => 'Subjects',
            'default_price' => 'Price per hour [€]',
            'default_extra' => 'Extra [€]',
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
            'default_subjects' => 'Materie',
            'default_price' => 'Prezzo orario [€]',
            'default_extra' => 'Extra [€]',
            'entry_password' => 'Password',
            'modify' => 'Modifica',
            'delete' => 'Elimina'
        ),
    );



    $result = Query("SELECT A.*, A.id_alumno as id, CONCAT(P.name, ' ', P.surname) as owner
    FROM PLM_Alumni as A
    JOIN PLM_Professor as P ON A.id_owner = P.id
    WHERE JSON_CONTAINS(id_auth_professors, '$id_professor', '$')");

    if ($result->num_rows) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $row['id_auth_professors'] = json_decode($row['id_auth_professors'], true);
            $row['default_subjects'] = json_decode($row['default_subjects'], true);
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


function getSubjectList(): array
{
    $subjectList = array();
    $merged = array();
    $title = array('en' => 'Subject list', 'it' => 'Elenco materie');
    $locale = array(
        'en' => array(),
        'it' => array()
    );

    $result = Query("SELECT default_subjects FROM PLM_Alumni");

    if ($result->num_rows) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $array = json_decode($row['default_subjects'], true);
            $merged = array_merge($merged, $array);
        }
    }

    foreach (array_count_values($merged) as $key => $value) {
        $subjectList[] = $key;
    }

    return array(
        'title' => $title,
        'type' => 'table',
        'table' => 'PLM_Alumni',
        'locale' => $locale,
        'data' => $subjectList
    );
}

<?php

$type = $RCV->type;

switch ($type) {
    case 'ProfessorDashBoard':
        $dumpType = $RCV->dumpType;
        if ($dumpType == 'limited') $nRow = 20;
        else $nRow = 1000;


        $id_professor = checkAuthorization();
        $getAlumniList = getAlumniList($id_professor);
        $getProfessorList = getProfessorList();
        $getSubjectList = getSubjectList();

        $getLessons = getLessons('id_professor', $id_professor, $nRow);

        $return_obj->Data->id = $id_professor;

        if ($getLessons['data']) {
            $return_obj->Data->dashboard = array(
                array(
                    getStats('id_professor', $id_professor),
                    getPendingPayments('id_professor', $id_professor),
                ),
                array(
                    getSubjectGraph('id_professor', $id_professor),
                    getMonthlyHoursGraph('id_professor', $id_professor),
                    getPaymentsDistribution('id_professor', $id_professor)
                ),
                array(
                    $getAlumniList,
                ),
                array(
                    $getLessons,
                    getPayments('id_professor', $id_professor, $nRow)
                )
            );
        } else {
            $return_obj->Data->dashboard = array(
                array(
                    array(
                        'title' => array(
                            'en' => 'No lessons recorded yet. Add one from the button above.',
                            'it' => 'Nessuna lezione registrata. Aggiungine una dal pulsante in alto.',
                        ),
                        'type' => 'graph',
                        'table' => 'PLM_Lessons_List',
                        'url' => 'https://t4.ftcdn.net/jpg/04/72/65/73/360_F_472657366_6kV9ztFQ3OkIuBCkjjL8qPmqnuagktXU.jpg'
                    )
                )
            );
        }

        $return_obj->Data->alumni_list = $getAlumniList['data'];
        $return_obj->Data->professor_list = $getProfessorList['data'];
        $return_obj->Data->subject_list = $getSubjectList['data'];
        $return_obj->Data->payment_type_list = ['Contanti', 'Bonifico', 'Altro'];

        break;


    case 'AlumnoDashBoard':
        $entry_password = $RCV->entry_password;
        $dumpType = $RCV->dumpType;
        if ($dumpType == 'limited') $nRow = 20;
        else $nRow = 1000;

        $result = Query("SELECT id_alumno FROM PLM_Alumni WHERE entry_password = '$entry_password'");

        if ($result->num_rows) {

            $id_alumno = $result->fetch_array(MYSQLI_ASSOC)['id_alumno'];
            if (empty($_COOKIE['token']) && empty($_COOKIE['PLM_token'])) Query("UPDATE PLM_Alumni SET last_login = NOW() WHERE id_alumno = '$id_alumno'");

            $return_obj->Data->id = $id_alumno;
            $return_obj->Data->dashboard = array(
                array(
                    getStats('id_alumno', $id_alumno)
                ),
                array(
                    getLessons('id_alumno', $id_alumno, $nRow),
                    getPayments('id_alumno', $id_alumno, $nRow)
                ),
                array(
                    getSubjectGraph('id_alumno', $id_alumno),
                    getMonthlyHoursGraph('id_alumno', $id_alumno)
                )
            );
        } else {
            die(returndata(1, "The alumno doesn't exist."));
        }

        break;
}

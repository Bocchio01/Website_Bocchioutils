<?php
include_once '_PLM_functions.php';

switch ($_POST["action"]) {

    case 'AlumnoLogin':
        $entry_password = $RCV->entry_password;

        $result = Query("SELECT id_alumno FROM PLM_Alumni WHERE entry_password = '$entry_password'");

        if ($result->num_rows) {

            $id_alumno = $result->fetch_array(MYSQLI_ASSOC)['id_alumno'];

            $return_obj->Data->id = $id_alumno;
            $return_obj->Data->dashboard = array(
                array(
                    getStats('id_alumno', $id_alumno)
                ),
                array(
                    getLessons('id_alumno', $id_alumno),
                    getPayments('id_alumno', $id_alumno)
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

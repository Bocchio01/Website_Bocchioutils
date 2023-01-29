<?php

$nameTable = $RCV->nameTable;
$id = $RCV->id;

switch ($nameTable) {

    case 'PLM_Alumni':
        $id_professor = checkAuthorization();

        // Check if the professor is authorized to delete the alumno
        $result = Query("SELECT id_alumno
                FROM PLM_Alumni
                WHERE id_alumno = $id
                AND JSON_CONTAINS(id_auth_professors, '$id_professor', '$')");
        if ($result->num_rows == 0) die(returndata(1, "You are not authorized to delete the selected alumno."));

        Query("DELETE FROM PLM_Alumni WHERE id_alumno = $id");

        break;

    case 'PLM_Lessons_List':
        $id_professor = checkAuthorization();

        $id_creator = Query("SELECT id_professor FROM PLM_Lessons_List WHERE id = $id")->fetch_array(MYSQLI_ASSOC)['id_professor'];
        if ($id_creator != $id_professor) die(returndata(1, "You are not authorized to delete this lesson."));

        Query("DELETE FROM PLM_Lessons_List WHERE id = $id");
        break;

    case 'PLM_Lessons_Payments':
        $id_professor = checkAuthorization();

        $id_creator = Query("SELECT id_professor FROM PLM_Lessons_Payments WHERE id = $id")->fetch_array(MYSQLI_ASSOC)['id_professor'];
        if ($id_creator != $id_professor) die(returndata(1, "You are not authorized to delete this payment."));

        Query("DELETE FROM PLM_Lessons_Payments WHERE id = $id");
        break;
}

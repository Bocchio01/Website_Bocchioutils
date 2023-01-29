<?php

$nameTable = $RCV->nameTable;
$id = $RCV->id;

switch ($nameTable) {

    case 'PLM_Alumni':
        $id_professor = checkAuthorization();

        // $id_auth_professors = json_encode($RCV->id_auth_professors);
        $name = $RCV->name;
        $surname = $RCV->surname;
        $email = $RCV->email;

        $default_subjects = $RCV->default_subjects;
        if (gettype($default_subjects) == "string") $default_subjects = json_encode(explode(",", $default_subjects));
        else $default_subjects = json_encode($default_subjects);


        $default_price = (int) $RCV->default_price;
        $default_extra = (int) $RCV->default_extra;

        $entry_password = $RCV->entry_password;

        // Check if the professor is authorized to modify the alumno
        $result = Query("SELECT id_alumno
            FROM PLM_Alumni
            WHERE id_alumno IN ($id)
            AND JSON_CONTAINS(id_auth_professors, '$id_professor', '$')");
        if ($result->num_rows == 0) die(returndata(1, "You are not authorized to modify the selected alumno."));


        Query("UPDATE PLM_Alumni SET
             name = '$name',
             surname = '$surname',
             email = '$email',
             default_subjects = '$default_subjects',
             default_price = $default_price,
             default_extra = $default_extra,
             entry_password = '$entry_password'
             WHERE id_alumno = $id");

        break;


    case 'PLM_Lessons_List':
        $id_professor = checkAuthorization();

        $arguments = $RCV->arguments;
        $date_lessons = $RCV->date_lessons;
        $extra = $RCV->extra;
        $minutes = $RCV->minutes;
        $price = $RCV->price;

        $subject = $RCV->subject;
        if (gettype($subject) == "string") $subject = json_encode(explode(",", $subject));
        else $subject = json_encode($subject);


        $id_creator = Query("SELECT id_professor FROM PLM_Lessons_List WHERE id = $id")->fetch_array(MYSQLI_ASSOC)['id_professor'];
        if ($id_creator != $id_professor) die(returndata(1, "You are not authorized to modify this lesson."));

        Query("UPDATE PLM_Lessons_List SET
             arguments = '$arguments',
             date_lessons = '$date_lessons',
             extra = $extra,
             minutes = $minutes,
             price = $price,
             subject = '$subject'
             WHERE id = $id");
        break;


    case 'PLM_Lessons_Payments':
        $id_professor = checkAuthorization();

        $amount = $RCV->amount;
        $date_received = $RCV->date_received;
        $location = $RCV->location;
        $type = $RCV->type;

        $id_creator = Query("SELECT id_professor FROM PLM_Lessons_Payments WHERE id = $id")->fetch_array(MYSQLI_ASSOC)['id_professor'];
        if ($id_creator != $id_professor) die(returndata(1, "You are not authorized to modify this payment."));

        Query("UPDATE PLM_Lessons_Payments SET
             amount = $amount,
             date_received = '$date_received',
             location = '$location',
             type = '$type'
             WHERE id = $id");
        break;
}

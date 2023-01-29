<?php

$type = $RCV->type;

switch ($type) {

    case 'Alumno':
        $id_owner = checkAuthorization();

        $id_auth_professors = json_encode($RCV->id_auth_professors);
        $name = $RCV->name;
        $surname = $RCV->surname;
        $email = $RCV->email;

        $default_subjects = json_encode($RCV->default_subjects);
        $default_price = (int) $RCV->default_price;
        $default_extra = (int) $RCV->default_extra;

        $entry_password = $name . '_' . $surname;


        $result = Query("INSERT
                INTO PLM_Alumni (id_owner, id_auth_professors, name, surname, email, default_subjects, default_price, default_extra, entry_password)
                VALUES ($id_owner, '$id_auth_professors', '$name', '$surname', '$email', '$default_subjects', $default_price, $default_extra, '$entry_password')");

        break;


    case 'Lesson':
        $id_professor = checkAuthorization();

        $id_alumni = (array) $RCV->id_alumni;
        $date = (string) $RCV->date;
        $minutes = (int) $RCV->minutes;
        $extra = (float) $RCV->extra;
        $price_per_hour = (float) $RCV->price_per_hour;
        $arguments = (string) $RCV->arguments;
        $subjects = json_encode($RCV->subjects);


        // Check if the professor is authorized to add lessons to the selected alumni
        $id_alumni_implode = implode(',', $id_alumni);
        $result = Query("SELECT id_alumno
            FROM PLM_Alumni
            WHERE id_alumno IN ($id_alumni_implode)
            AND JSON_CONTAINS(id_auth_professors, '$id_professor', '$')");
        if ($result->num_rows != count($id_alumni)) die(returndata(1, "You are not authorized to add lessons to the selected alumni."));


        $price = calculatePrice($minutes, $price_per_hour);
        foreach ($id_alumni as $key => $id_alumno) {
            Query("INSERT
                 INTO PLM_Lessons_List (id_alumno, id_professor, date_lessons, minutes, price, extra, subject, arguments)
                 VALUES ($id_alumno, $id_professor, '$date', $minutes, $price, $extra, '$subjects', '$arguments')");
        }
        break;


    case 'Payment':
        $id_professor = checkAuthorization();

        $id_alumno = (int) $RCV->id_alumno;
        $date = (string) $RCV->date;
        $amount = (float) $RCV->amount;
        $location = (string) $RCV->location;
        $payment_type = (string) $RCV->payment_type;

        // Check if the professor is authorized to add payments to the selected alumni
        $result = Query("SELECT id_alumno
            FROM PLM_Alumni
            WHERE id_alumno IN ($id_alumno)
            AND JSON_CONTAINS(id_auth_professors, '$id_professor', '$')");
        if ($result->num_rows == 0) die(returndata(1, "You are not authorized to add payments to the selected alumno."));

        Query("INSERT
            INTO PLM_Lessons_Payments (id_alumno, id_professor, date_received, amount, location, type)
            VALUES ($id_alumno, $id_professor, '$date', $amount, '$location', '$payment_type')");
        break;
}

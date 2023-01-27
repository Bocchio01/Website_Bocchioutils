<?php

include_once '_PLM_functions.php';

switch ($_POST["action"]) {

    case 'ProfessorGetDashBoard':
        $id_professor = checkAuthorization();
        $getAlumniList = getAlumniList($id_professor);

        $return_obj->Data->id = $id_professor;
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
                getLessons('id_professor', $id_professor),
                getPayments('id_professor', $id_professor)
            )
        );
        $return_obj->Data->alumni_list = $getAlumniList['data'];

        break;


    case 'Add':
        $type = $RCV->type;

        switch ($type) {

            case 'Alumno':
                $id_owner = checkAuthorization();

                $id_auth_professors = json_encode($RCV->id_auth_professors);
                $name = $RCV->name;
                $surname = $RCV->surname;
                $email = $RCV->email;

                $default_price = (int) $RCV->defaultPrice;
                $default_extra = (int) $RCV->extra;

                $entry_password = $name . '_' . $surname;

                $result = Query("INSERT
                    INTO PLM_Alumni (id_owner, id_auth_professors, name, surname, email, default_price, default_extra, entry_password)
                    VALUES ($id_owner, '$id_auth_professors', '$name', '$surname', '$email', $default_price, $default_extra, '$entry_password')");

                break;


            case 'Lesson':
                $id_professor = checkAuthorization();

                $id_alumni = (array) $RCV->id_alumni;
                $date = (string) $RCV->date;
                $minutes = (int) $RCV->minutes;
                $extra = (float) $RCV->extra;
                $price_per_hour = (float) $RCV->price_per_hour;
                $arguments = (string) $RCV->arguments;
                $subjects = (array) json_encode($RCV->subjects);


                // Check if the professor is authorized to add lessons to the selected alumni
                $id_alumni_implode = implode(',', $id_alumni);
                $result = Query("SELECT id_alumno
                FROM PLM_Alumni
                WHERE id_alumno IN ($id_alumni_implode)
                AND JSON_CONTAINS(id_auth_professors, $id_professor, '$')");
                if ($result->num_rows == count($id_alumni)) die(returndata(1, "You are not authorized to add lessons to the selected alumni."));


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
                $type = (string) $RCV->type;

                // Check if the professor is authorized to add payments to the selected alumni
                $result = Query("SELECT id_alumno
                FROM PLM_Alumni
                WHERE id_alumno IN ($id_alumno)
                AND JSON_CONTAINS(id_auth_professors, $id_professor, '$')");
                if ($result->num_rows == 0) die(returndata(1, "You are not authorized to add payments to the selected alumno."));

                Query("INSERT
                INTO PLM_Payments_List (id_alumno, id_professor, date_payment, amount, location, type)
                VALUES ($id_alumno, $id_professor, '$date', $amount, '$location', '$type')");
                break;
        }



    case 'Modify':
        $nameTable = $RCV->nameTable;
        $id = $RCV->id;

        switch ($nameTable) {

            case 'PLM_Alumni':
                $id_professor = checkAuthorization();

                // $id_auth_professors = json_encode($RCV->id_auth_professors);
                $name = $RCV->name;
                $surname = $RCV->surname;
                $email = $RCV->email;

                $default_price = (int) $RCV->default_price;
                $default_extra = (int) $RCV->default_extra;

                $entry_password = $RCV->entry_password;

                // Check if the professor is authorized to modify the alumno
                $result = Query("SELECT id_alumno
                FROM PLM_Alumni
                WHERE id_alumno IN ($id)
                AND JSON_CONTAINS(id_auth_professors, $id_professor, '$')");
                if ($result->num_rows == 0) die(returndata(1, "You are not authorized to modify the selected alumno."));


                Query("UPDATE PLM_Alumni SET
                 name = '$name',
                 surname = '$surname',
                 email = '$email',
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

        break;


    case 'Delete':
        $nameTable = $RCV->nameTable;
        $id = $RCV->id;

        switch ($nameTable) {

            case 'PLM_Alumni':
                $id_professor = checkAuthorization();

                // Check if the professor is authorized to delete the alumno
                $result = Query("SELECT id_alumno
                FROM PLM_Alumni
                WHERE id_alumno IN ($id)
                AND JSON_CONTAINS(id_auth_professors, $id_professor, '$')");
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
        break;
}

<?php

// obj{
//     Status: (0=ok, 1=err)
//     Data:   obj{
//                ...
//             }
//     Log: Utente non in inserito..
// }


include "setting.php";
if (isset($_POST['data'])) $RCV = json_decode($_POST['data']);


include "UserAction.php";
include "ForumAction.php";

switch ($_POST["action"]) {

    case 'GetAllData':
        $table = $RCV->table;

        GetAllData($conn, $table, $return_obj);
        break;

    case 'InteractionsUpdate':
        $month = date("m");
        $year = date("Y");
        $result = Query($conn, "SELECT * FROM PWS_Interactions WHERE (month, year) IN (($month,$year))", $return_obj);

        // If month-year row hasn't been created yet
        if ($result->num_rows == 0) Query($conn, "INSERT INTO PWS_Interactions (month, year) VALUES ($month,$year)", $return_obj);
        $keys = array_keys((array)$RCV);
        $values = array_values((array)$RCV);

        for ($i = 0; $i < count($keys); $i++) {
            # code...
            $url_ricevuto = $keys[$i];
            $result = Query($conn, "SELECT id_page FROM PWS_Pages WHERE url='$url_ricevuto' limit 1", $return_obj);
            $id_page = $result->fetch_array(MYSQLI_ASSOC)['id_page'];
            $id_page_ = $id_page . '_';

            // If page has never been registrer before on database
            if ($result->num_rows == 0) {
                // Create record in PWS_Pages table
                Query($conn, "INSERT INTO PWS_Pages (url) VALUES ('$url_ricevuto')", $return_obj);

                $result = Query($conn, "SELECT id_page FROM PWS_Pages WHERE url='$url_ricevuto' limit 1", $return_obj);
                $id_page = $result->fetch_array(MYSQLI_ASSOC)['id_page'];
                $id_page_ = $id_page . '_';

                // Add column to PWS_Interactions
                Query($conn, "ALTER TABLE PWS_Interactions ADD COLUMN $id_page_ INT DEFAULT 0", $return_obj);
            }

            Query($conn, $sql = "UPDATE PWS_Pages SET interactions=interactions+$values[$i] WHERE id_page=$id_page", $return_obj);
            Query($conn, "UPDATE PWS_Interactions SET $id_page_=$id_page_+$values[$i] WHERE (month, year) IN (($month,$year))", $return_obj);
        }

        break;

    case 'GetAllFile':
        # code...
        $result = Query($conn, "SELECT attachment, url FROM PWS_Pages", $return_obj);
        // $return_obj->Data = array();
        if ($result->num_rows) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $return_obj->Data->{$row['url']} = json_decode($row['attachment']);
            }
        } else {
            $return_obj->Log[] = "The table selected is empty";
        }
        break;
        

    default:
        // die(returndata($return_obj, 1, "No action selected"));

        break;
}

$conn->close();
$return_obj->Log[] = "Connection with MySQL database closed";
returndata($return_obj);

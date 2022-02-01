<?php

// obj{
//     Status: (0=ok, 1=err)
//     Data:   obj{
//                ...
//             }
//     Log: Utente non in inserito..
// }


include_once "../setting.php";

if (isset($_POST['data'])) $RCV = json_decode($_POST['data']);


include_once "UserAction.php";
include_once "ForumAction.php";

switch ($_POST["action"]) {

    case 'GetAllData':
        $table = $RCV->table;

        GetAllData($table);
        break;

    case 'InteractionsUpdate':
        $date = (string) date("m_Y");


        $exist = false;
        $result = Query("DESC PWS_interactions");
        while ($name_field = $result->fetch_array(MYSQLI_ASSOC)['Field']) {
            if ($name_field == $date) {
                $exist = true;
                break;
            }
        }


        if (!$exist) {
            // Create column for current date
            $return_obj->Log[] = "Add column for: $date";
            Query("ALTER TABLE PWS_Interactions ADD COLUMN $date JSON DEFAULT '{\"IT\":0,\"EN\":0,\"JP\":0}'");
        }


        // $keys = array_keys((array) $RCV);
        // $values = array_values((array) $RCV);

        // for ($i = 0; $i < count($keys); $i++) {

        // list($id_page, $lang, $url) =  GetIdLang($keys[$i]);
        list($id_page, $lang, $url) =  GetIdLang($RCV);

        if (!$id_page) {
            // Articolo non ancora registrato sul database
            $return_obj->Log[] = "Articolo non ancora registrato sul database";
            Query("INSERT INTO PWS_Pages (name) VALUES ('$url')");
            $id_page = Query("SELECT LAST_INSERT_ID() AS id_page")->fetch_array(MYSQLI_ASSOC)['id_page'];
            Query("INSERT INTO PWS_Traduction (id_page, $lang) VALUES ($id_page, '$url')");
            Query("INSERT INTO PWS_Interactions (id_page) VALUES ($id_page)");
        }

        $result = Query("SELECT $date, id FROM PWS_Interactions WHERE id_page = $id_page");

        $row = $result->fetch_array(MYSQLI_ASSOC);
        // $newobj = AddToObj($row[$date], $lang, $values[$i]);
        $newobj = AddToObj($row[$date], $lang, 1);

        Query("UPDATE PWS_Interactions SET $date = '$newobj' WHERE id = $row[id]");
        // }
        break;


    case 'NavigationGetFiles':
        list($id_page, $lang, $url) =  GetIdLang($RCV->url);

        $result = Query("SELECT attachment FROM PWS_Pages WHERE id_page = $id_page");

        $return_obj->Data = json_decode($result->fetch_array(MYSQLI_ASSOC)['attachment']);
        break;


    default:
        // $return_obj->Log[] = "No action selected";

        // die(returndata(1, "No action selected"));

        break;
}

$conn->close();
returndata(0, "Connection with MySQL database closed");

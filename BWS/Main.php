<?php

include_once "../_setting.php";



function getPost()
{
    if (!empty($_POST)) {
        // when using application/x-www-form-urlencoded or multipart/form-data as the HTTP Content-Type in the request
        // NOTE: if this is the case and $_POST is empty, check the variables_order in php.ini! - it must contain the letter P
        return $_POST;
    }

    // when using application/json as the HTTP Content-Type in the request
    $post = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() == JSON_ERROR_NONE) {
        return $post;
    }

    return [];
}

$_POST = getPost();

// if (empty($_SERVER['HTTP_ORIGIN'])) {
//     header('Content-Type: text/html; charset=utf-8');
// } else {
if (isset($_POST['data']) && is_string($_POST['data'])) $RCV = json_decode($_POST['data']);
else $RCV = (object) $_POST['data'];
// }

// if (isset($_POST['data'])) $RCV = json_decode($_POST['data']);

include_once "UserAction.php";
include_once "ForumAction.php";

switch ($_POST["action"]) {

    case 'GetAllData':
        $table = $RCV->table;

        GetAllData($table);
        break;


    case 'InteractionsUpdate':
        $year = date("Y");
        $month = (int) date("m") - 1;

        if (!Query("SHOW COLUMNS FROM BWS_Interactions LIKE '$year'")->num_rows) {
            Query("ALTER TABLE BWS_Interactions ADD COLUMN `$year` JSON DEFAULT ('[{},{},{},{},{},{},{},{},{},{},{},{}]')");
        }

        if (isset($_COOKIE['token']) && Query("SELECT id_user FROM BWS_Users WHERE token = '$_COOKIE[token]'")->fetch_array(MYSQLI_ASSOC)['id_user'] == 2) break;

        list($id_page, $lang, $url, $param) =  GetIdLang($RCV);

        if (!Query("SELECT JSON_EXTRACT(`$year`, '$[$month].$lang') as is_null FROM BWS_Interactions WHERE id_page = $id_page")->fetch_array(MYSQLI_ASSOC)['is_null']) {
            Query("UPDATE BWS_Interactions SET `$year`=JSON_SET(`$year`, '$[$month].$lang', 1) WHERE id_page = $id_page");
        } else {
            Query("UPDATE BWS_Interactions SET `$year`=JSON_SET(`$year`, '$[$month].$lang', JSON_EXTRACT(`$year`, '$[$month].$lang') + 1) WHERE id_page = $id_page");
        }
        break;


    case 'NavigationGetFiles':
        list($id_page, $lang, $url) =  GetIdLang($RCV->url);

        $result = Query("SELECT attachment FROM BWS_Pages WHERE id_page = $id_page");

        $return_obj->Data = json_decode($result->fetch_array(MYSQLI_ASSOC)['attachment']);
        break;
}

$conn->close();
returndata(0, "Connection with MySQL database closed");

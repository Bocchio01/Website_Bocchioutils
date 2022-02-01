<?php

// Functions
function returndata(int $code = 0, string $log = null)
{
    global $return_obj;
    $return_obj->Log[] = $log;
    $return_obj->Status = $code;
    echo json_encode($return_obj);
}

function isData(array $value)
{
    for ($i = 0; $i < count($value); $i++) {
        if (!isset($_POST[$value[$i]])) {
            die(returndata(1, "Data not received -> " . $value[$i]));
        }
    }
}

function Query(string $sql)
{
    global $conn;
    if (!$result = $conn->query($sql)) {
        die(returndata(1, $conn->error));
    } else {
        return $result;
    }
}

function GetAllData($table)
{
    global $return_obj;
    $result = Query("SELECT * FROM $table");
    $return_obj->Data = array();
    if ($result->num_rows) while ($row = $result->fetch_array(MYSQLI_ASSOC)) $return_obj->Data[] = $row;
    else $return_obj->Log[] = "The table selected is empty";
}

function GetIdLang($url)
{
    $lang = 'IT';
    if (strlen($url) > 3) {
        if (substr($url, -1) != '/') $url .= '/';
        if ($url[3] == '/') $lang = strtoupper(substr($url, 1, 3));
    }

    $result = Query("SELECT id_page FROM PWS_Traduction WHERE $lang LIKE '$url'")->fetch_array(MYSQLI_ASSOC)['id_page'];

    return [$result, $lang, $url];
}

function AddToObj($obj_string, $target, $value)
{
    $obj = json_decode($obj_string);
    $obj->{$target} += $value;
    $newobj = json_encode($obj);
    return $newobj;
}

function CreateToken(int $lenght = 15)
{

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $token = '';
    for ($i = 0; $i < $lenght; $i++) $token .= $characters[rand(0, strlen($characters) - 1)];

    return $token;
}

function render($script, array $vars = array())
{
    extract($vars);

    ob_start();
    include $script;
    return ob_get_clean();
}


function ForumGetPost($url)
{
    global $return_obj;
    list($id_page, $lang, $url) =  GetIdLang($url);

    $isForum = Query("SELECT forum FROM PWS_Pages WHERE id_page='$id_page' limit 1")->fetch_array(MYSQLI_ASSOC)['forum'];
    if ($isForum) {
        $return_obj->Data->isForum = 1;

        $posts = Query("SELECT u.nickname, u.avatar, f.* FROM PWS_Users AS u JOIN PWS_Forum AS f WHERE u.id_user = f.id_user AND f.id_page=$id_page ORDER BY f.refer, f.id_post");
        while ($row = $posts->fetch_array(MYSQLI_ASSOC)) $return_obj->Data->Posts[] = $row;
    } else $return_obj->Data->isForum = 0;
}

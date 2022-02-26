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
    // for ($i = 0; $i < count($value); $i++) {
    //     if (!isset($_POST[$value[$i]])) {
    //         die(returndata(1, "Data not received -> " . $value[$i]));
    //     }
    // }
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
    $params = array();
    foreach (['loading', 'standalone'] as $param) {
        if (strpos($url, '?' . $param . '=true')) {
            $params[$param] = true;
            $url = str_replace('?' . $param . '=true', '', $url);
        }
    }

    if (substr($url, -1) != '/') $url .= '/';
    if (strlen($url) > 3 && $url[3] == '/') $lang = substr($url, 1, 2);
    else $lang = 'en';

    $id_page = Query("SELECT id_page FROM BWS_Translations WHERE $lang LIKE '$url'")->fetch_array(MYSQLI_ASSOC)['id_page'];

    if (!$id_page) $id_page = 1;

    return [$id_page, $lang, $url, $params];
}

// function AddToObj($obj_string, $target, $value)
// {
//     $obj = json_decode($obj_string);
//     $obj->{$target} += $value;
//     $newobj = json_encode($obj);
//     return $newobj;
// }

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

    $style = "<style>
        h1 {
            text-align: center;
            font-size: 25px;
            margin-bottom: 20px;
        }

        p {
            font-size: 15px;
        }
    </style>";

    ob_start();
    include $script;
    return ob_get_clean();
}


function ForumGetPost($url)
{
    global $return_obj;
    list($id_page, $lang, $url) =  GetIdLang($url);

    $isForum = Query("SELECT forum FROM BWS_Pages WHERE id_page='$id_page' limit 1")->fetch_array(MYSQLI_ASSOC)['forum'];
    if ($isForum) {
        $return_obj->Data->isForum = 1;

        $posts = Query("SELECT u.nickname, u.avatar, f.* FROM BWS_Users AS u JOIN BWS_Forum AS f WHERE (u.id_user = f.id_user OR (f.id_user IS NULL AND u.id_user = 1)) AND f.id_page=$id_page ORDER BY f.refer, f.id_post");
        while ($row = $posts->fetch_array(MYSQLI_ASSOC)) $return_obj->Data->Posts[] = $row;
    } else $return_obj->Data->isForum = 0;
}

function ClearCookie()
{
    setcookie('token', "", [
        'path' => "/",
        'samesite' => 'None',
        'secure' => 'Secure',
        'httponly' => false,
    ]);
}

function GetLangSubdomanin()
{
    global $lang;
    // var_dump($_SERVER);
    // _SERVER array(51) { 
    //     ["HTTP_REFERER"]=> string(39) "http://it.localhost/BWS/it/database.php" 
    //     ["REQUEST_URI"]=> string(8) "/BWS/it/"
    //     ["SCRIPT_NAME"]=> string(17) "/BWS/it/index.php"
    //     ["PHP_SELF"]=> string(17) "/BWS/it/index.php"
    // }

    $url_array = explode('.', $_SERVER['HTTP_HOST']);
    $locale = array_shift($url_array);

    if (!in_array($locale, $lang)) {
        $locale = "en";
        // $otherlocale = "it";
    } else {
        // $otherlocale = $lang[!array_search($locale, $lang)];
    }

    return $locale;
}

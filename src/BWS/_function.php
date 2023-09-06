<?php

function GetIdLang($complete_url)
{
    $url = parse_url($complete_url, PHP_URL_PATH);
    $query = parse_url($complete_url, PHP_URL_QUERY);

    $params = array();
    foreach (['loading', 'standalone'] as $param) {
        if (strpos('?' . $query, $param)) $params[$param] = true;
    }

    if (substr($url, -1) != '/') $url .= '/';
    if (strlen($url) > 3 && $url[3] == '/') $lang = substr($url, 1, 2);
    else $lang = 'en';

    $id_page = Query("SELECT id_page FROM BWS_Translations WHERE $lang LIKE '$url'")->fetch_array(MYSQLI_ASSOC)['id_page'];

    if (!$id_page) $id_page = 1;

    return [$id_page, $lang, $url, $params];
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

    $style = file_get_contents(UTILS_SITE . '/BWS/template/style.css',);

    ob_start();
    include $script;
    return ob_get_clean();
}

function Cookie($cookie_value)
{
    setcookie('token', "$cookie_value", [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'samesite' => 'None',
        'secure' => 'Secure',
        'httponly' => false,
    ]);
}

function ClearCookie()
{
    setcookie('token', '', [
        'path' => '/',
        'samesite' => 'None',
        'secure' => 'Secure',
        'httponly' => true,
    ]);
}

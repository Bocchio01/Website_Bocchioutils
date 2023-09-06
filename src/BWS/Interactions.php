<?php

use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/BWS/interactions', function (Request $request, Response $response, $args) {
    $RCV = (object) $request->getParsedBody();

    $year = date("Y");
    $month = (int) date("m") - 1;

    if (!Query("SHOW COLUMNS FROM BWS_Interactions LIKE '$year'")->num_rows) {
        Query("ALTER TABLE BWS_Interactions ADD COLUMN `$year` JSON DEFAULT ('[{},{},{},{},{},{},{},{},{},{},{},{}]')");
    }

    if (isset($_COOKIE['token']) && Query("SELECT id_user FROM BWS_Users WHERE token = '$_COOKIE[token]'")->fetch_array(MYSQLI_ASSOC)['id_user'] == 2) return;

    list($id_page, $lang, $url, $param) =  GetIdLang($RCV);

    if (!Query("SELECT JSON_EXTRACT(`$year`, '$[$month].$lang') as is_null FROM BWS_Interactions WHERE id_page = $id_page")->fetch_array(MYSQLI_ASSOC)['is_null']) {
        Query("UPDATE BWS_Interactions SET `$year`=JSON_SET(`$year`, '$[$month].$lang', 1) WHERE id_page = $id_page");
    } else {
        Query("UPDATE BWS_Interactions SET `$year`=JSON_SET(`$year`, '$[$month].$lang', JSON_EXTRACT(`$year`, '$[$month].$lang') + 1) WHERE id_page = $id_page");
    }

    $response->getBody()->write(json_encode(['status' => 0]));
    return $response;
});

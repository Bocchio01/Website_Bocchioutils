<?php

use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/BWS/forum', function (RouteCollectorProxy $group) {

    $group->delete('/{id}', function (Request $request, Response $response, $args) {
        Query("DELETE FROM BWS_Forum WHERE id_post = $args[id] OR refer = $args[id]");

        $response->getBody()->write($args['id']);
        return $response;
    });

    $group->post('/ForumModifyPost', function (Request $request, Response $response, $args) {
        // Translate your 'ForumModifyPost' logic here
    });

    $group->post('/ForumAwnserPost', function (Request $request, Response $response, $args) {
        // Translate your 'ForumAwnserPost' logic here
    });

    $group->post('/ForumNewPost', function (Request $request, Response $response, $args) {
        // Translate your 'ForumNewPost' logic here
    });

    $group->post('/ForumGetPost', function (Request $request, Response $response, $args) {
        // Translate your 'ForumGetPost' logic here
    });
});

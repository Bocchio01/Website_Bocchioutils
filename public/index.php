<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();


$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');

// require_once __DIR__ . '/../src/bootstrap.php';

$directory = __DIR__ . "/../src/BWS/";
$files = glob($directory . "*.php");

foreach ($files as $file) {
    require_once $file;
}


$app->run();

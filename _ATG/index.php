<?php

require_once 'around_the_globe.php';

$point1 = [
    'latitudine' => 0,
    'longitudine' => 0
];

$point2 = [
    'latitudine' => 45,
    'longitudine' => 90
];

$around_the_globe = new AroundTheGlobe();
$around_the_globe->init([$point1, $point2]);

$response = [
    'result' => $around_the_globe->result
];

header('Content-Type: application/json');
echo json_encode($response);

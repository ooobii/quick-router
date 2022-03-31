<?php
require_once 'vendor/autoload.php';

use ooobii\QuickRouter\Router\Router;
use ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE;

$testRouter = new Router('/testApi/');
$testRouter->setErrorRoute(500, function($exception) {
    echo json_encode(['error' => 'Exception', 'message' => $exception->getMessage(), 'code' => $exception->getCode()]);
});


//GET REQUESTS
$testRouter->addRoute(HTTP_REQUEST_TYPE::GET, '/', function($input) {
    return ['response' => 'This is the root of the API!'];
}, TRUE);
$testRouter->addRoute(HTTP_REQUEST_TYPE::GET, '/test', function($input) {
    return ['response' => 'This is the test endpoint!'];
}, TRUE);
$testRouter->addRoute(HTTP_REQUEST_TYPE::GET, '/test/dump', function($input) {
    return $this;
}, FALSE);
$testRouter->addRoute(HTTP_REQUEST_TYPE::GET, '/test/is_cli', function($input) {
    return $this->isCLI();
}, TRUE);
$testRouter->addRoute(HTTP_REQUEST_TYPE::GET, '/test/router_force_json', function($input) {
    return $this->isJSON();
}, TRUE);


//POST REQUESTS



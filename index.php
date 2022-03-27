<?php
require_once __DIR__ . '/vendor/autoload.php';

use ooobii\QuickRouter\Router\Router;
use ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE;

//define a router class that handles all requests that originate from the root '/Api'
//paths / URIs are relative to the root, and case-insensitive when checking for qualified routes.
$router = new Router('/Api', TRUE);



//you can define handlers that accept exceptions thrown by the router for specific HTTP error codes.
$router->setErrorRoute(404, function($exception) {
    if($exception instanceof \Exception && !empty($exception->getMessage())) {
        $message = $exception->getMessage();
    } else {
        $message = 'Not Found';
    }

    echo json_encode(['error' => $message]);
}, TRUE);

//define routes for server errors encountered while processing route handlers.
$router->setErrorRoute(500, function($exception) {
    echo json_encode(['error' => 'Exception', 'message' => $exception->getMessage(), 'code' => $exception->getCode()]);
});



//here we add a handler for accessing router's root directly.
$router->addRoute(HTTP_REQUEST_TYPE::GET, '/', function($input) {
    return ['response' => 'This is the root of the API!'];
}, TRUE);

//you can also add handlers for folders / sub folders.
$router->addRoute(HTTP_REQUEST_TYPE::GET, '/php/info', function($input) {
    return phpinfo();
});



//parameters surrounded in brackets can be included in route paths. 
//When the route is executed, they will be added to `$input`. Parameters provided in the URL path
//will override any parameters provided in the query string or body of the request.
$router->addRoute(HTTP_REQUEST_TYPE::GET, '/users/{id}/{action}', function($input) {

    //make sure the user id is included in the request, and is an integer
    if(!isset($input['id']) || !is_numeric($input['id'])) throw new \Exception("Invalid user ID.");

    //convert input id to integer
    $id = intval($input['id']);

    //check if user exists (and handle as 404 error if not)
    if($id !== 1) {
        throw new \Exception("User not found.", 404);
    }

    return ['id' => $id, 'name' => 'Some User Name', 'action' => $input['action']];
});



//execute the router, and check if a route was found.
//if no route was found, `process()` will return FALSE. This is so multiple routers can handle
//requests in parallel. Adding this check to the last router executed will ensure that a response is always
//handled.
if(!$router->process()) {

    //no route was found for the request, trigger a 404 error.
    $router->handleError(404);

}


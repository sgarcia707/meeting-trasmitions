<?php
error_reporting(E_ALL); 
ini_set("display_errors", 1); 

$GLOBALS['url'] = __DIR__;

include_once 'Streaming/Brodcasting/brodcasting.php';
include_once 'vendor/autoload.php';

$c = new \Slim\Container(); //Create Your container


$app = new \Slim\App([
'settings' => [
    'determineRouteBeforeAppMiddleware' => false,
    'displayErrorDetails' => true,
    'addContentLengthHeader' => false
],
]);


$c = $app->getContainer();



//Override the default Not Found Handler
$c['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found');
    };
};

$c['phpErrorHandler'] = function ($c) {
    return function ($request, $response, $error) use ($c) {
        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    };
};

$app->get('/', function ($request, $response) {
    return $response->withRedirect("home.php");
});

$app->get('/configuration/ffmpeg', function ($request, $response) {
    $brodcasting = new Brodcasting();
    $data = $brodcasting->getConfigurationFfmpeg();
    return $response->withJson($data, 200);
});

$app->put('/configuration/ffmpeg/update', function ($request, $response) {
    $data = json_decode($request->getBody());

    $brodcasting = new Brodcasting();

    $condition = $data->condition;
    $json = $data->json;

    $brodcasting->updateConfigurationFfmpeg($condition, $json);
});

$app->get('/list/broadcasting', function ($request, $response) {
    $brodcasting = new Brodcasting();
    $data = $brodcasting->listBroadcast();
    return $response->withJson($data, 201);
});

$app->run();


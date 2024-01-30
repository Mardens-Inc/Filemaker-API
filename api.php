<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Filemaker\FileMaker;

require_once './vendor/autoload.php';
require_once './inc/Filemaker.inc.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->get("/databases", function(Request $request, Response $response, $args){
    $response->getBody()->write(json_encode($request->getHeader("request-options")));
    return $response;
});


$app->run();


/**
 * Parses the given headers string into an array.
 *
 * @param string $headers The headers string to parse.
 * @return array|false Returns the parsed headers as an array or false if an error occurs.
 */
function parseHeaders(string $headers): array|false
{
    try {
        return json_decode($headers, true);
    } catch (Exception $e) {
        return false;
    }
}
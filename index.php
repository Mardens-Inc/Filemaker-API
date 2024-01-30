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
$app->setBasePath("/fmutil");

$app->get("/", function (Request $request, Response $response, $args) {
    $response->getBody()->write(file_get_contents("js/Filemaker.js"));
    header("Content-Type: text/javascript");
    return $response;
});
$app->get("/js/minified", function (Request $request, Response $response, $args) {
    $response->getBody()->write(file_get_contents("js/Filemaker.min.js"));
    header("Content-Type: text/javascript");
    return $response;
});

$app->get("/js/time", function (Request $request, Response $response, $args) {
    $response->getBody()->write(filemtime("js/Filemaker.js") . "");
    return $response;
});

$app->get("/databases", function (Request $request, Response $response, $args) {
    $authentication = parseAuthHeaders($request);
    if (!$authentication) {
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        header("Content-Type: application/json");
        return $response;
    }
    $username = $authentication["username"];
    $password = $authentication["password"];

    $databases = Filemaker::getDatabases($username, $password);

    $response->getBody()->write(json_encode($databases));
    header("Content-Type: application/json");
    return $response;
});

$app->get("/databases/{database}/layouts", function (Request $request, Response $response, $args) {
    $authentication = parseAuthHeaders($request);
    if (!$authentication) {
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        header("Content-Type: application/json");
        return $response;
    }
    $username = $authentication["username"];
    $password = $authentication["password"];

    $database = $args["database"];

    $layouts = Filemaker::getLayouts($username, $password, $database);

    $response->getBody()->write(json_encode($layouts));
    header("Content-Type: application/json");
    return $response;
});

$app->get("/databases/{database}/layouts/{layout}/fields", function (Request $request, Response $response, $args) {
    $authentication = parseAuthHeaders($request);
    if (!$authentication) {
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        header("Content-Type: application/json");
        return $response;
    }
    $username = $authentication["username"];
    $password = $authentication["password"];

    $database = $args["database"];
    $layout = $args["layout"];
    $fm = new FileMaker($username, $password, $database, $layout);
    $fields = $fm->getRowNames();

    $response->getBody()->write(json_encode($fields));
    header("Content-Type: application/json");
    return $response;
});


$app->run();


/**
 * Parse the authentication headers from the given request object.
 *
 * @param Request $request The request object containing the headers.
 * @return array|false The parsed authentication headers as an array, or false if the headers could not be parsed.
 */
function parseAuthHeaders(Request $request): array|false
{
    $headers = @$request->getHeader("X-Authentication-Options");

    // Make sure headers are not empty
    if (count($headers) < 1) {
        return false;
    }

    // Get the first header
    $headers = $headers[0];

    // Check if the headers is not null
    if (!$headers) {
        return false;
    }

    // Try to parse the headers as json
    try {
        return json_decode($headers, true);
    } catch (Exception $e) {
        return false;
    }
}
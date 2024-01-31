<?php

use Filemaker\FilemakerMemory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Filemaker\FileMaker;

require_once './vendor/autoload.php';
require_once './inc/Filemaker.inc.php';
require_once './inc/FilemakerMemory.php';

FilemakerMemory::init();

// Initialize a new Slim App
$app = AppFactory::create();

// Adds routing middleware to the application
$app->addRoutingMiddleware();

// Adds error handling middleware to the application
// The parameters are: displayErrorDetails, logErrors, logErrorDetails
$app->addErrorMiddleware(true, true, true);

// Sets the base path for the application
$app->setBasePath("/fmutil");


$app->delete("/database/{database}", function (Request $request, Response $response, $args) {
    $database = $args["database"];
    // Parse the authentication headers from the request
    $authentication = parseAuthHeaders($request);

    // Check if the parsed authentication is valid
    if (!$authentication) {
        // Write the error response if authentication is invalid
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        // Return the response
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    // Extract the credentials from the parsed authentication
    $username = $authentication["username"];
    $password = $authentication["password"];

    try {
        @Filemaker::deleteDatabase($database, $username, $password);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["error" => "Unknown issue has occurred", "message" => $e->getMessage()]));

        // Set the response content type to JSON
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }


    return $response->withStatus(200)->withHeader("Content-Type", "application/json");
});

$app->delete("/database/{database}/layout/{layout}/record", function (Request $request, Response $response, $args) {
    $database = $args["database"];
    $layout = $args["layout"];
    // Parse the authentication headers from the request
    $authentication = parseAuthHeaders($request);

    // Check if the parsed authentication is valid
    if (!$authentication) {
        // Write the error response if authentication is invalid
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        // Return the response
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    // Extract the credentials from the parsed authentication
    $username = $authentication["username"];
    $password = $authentication["password"];

    try {
        $fm = new Filemaker($username, $password, $database, $layout);
        $fm->clearDatabase();
        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["error" => "Unknown issue has occurred", "message" => $e->getMessage()]));

        // Set the response content type to JSON
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }
});

$app->delete("/database/{database}/layout/{layout}/record/{id}", function (Request $request, Response $response, $args) {
    $database = $args["database"];
    $layout = $args["layout"];
    $id = $args["id"];
    try {
        $id = @intval($id);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["error" => "Invalid ID"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }
    // Parse the authentication headers from the request
    $authentication = parseAuthHeaders($request);

    // Check if the parsed authentication is valid
    if (!$authentication) {
        // Write the error response if authentication is invalid
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        // Return the response
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    // Extract the credentials from the parsed authentication
    $username = $authentication["username"];
    $password = $authentication["password"];

    try {
        $fm = new Filemaker($username, $password, $database, $layout);
        $fm->deleteRecord($id);
        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["error" => "Unknown issue has occurred", "message" => $e->getMessage()]));

        // Set the response content type to JSON
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }
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
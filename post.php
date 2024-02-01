<?php

use Filemaker\FilemakerMemory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Filemaker\FileMaker;

require_once './vendor/autoload.php';
require_once './inc/Filemaker.inc.php';
require_once './inc/FilemakerMemory.php';
require_once './restUtility.php';


// Initialize a new Slim App
$app = AppFactory::create();

// Adds routing middleware to the application
$app->addRoutingMiddleware();

// Adds error handling middleware to the application
// The parameters are: displayErrorDetails, logErrors, logErrorDetails
$app->addErrorMiddleware(true, true, true);

// Sets the base path for the application
$app->setBasePath("/fmutil");

// Define a new route for POST requests to /databases/{database}/layouts/{layout}/records[/{id}]
$app->post("/databases/{database}/layouts/{layout}/records[/{id}]", function (Request $request, Response $response, $args) {

    // Extract and validate authorization details from the provided headers
    $authentication = parseAuthHeaders($request);
    if (!$authentication) {
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    // Extract username, password, database, and layout from the provided arguments and request
    $username = $authentication["username"];
    $password = $authentication["password"];
    $database = $args['database'];
    $layout = $args['layout'];

    // Parse the request body and query parameters
    $data = $request->getParsedBody();
    $params = $request->getQueryParams();

    // Decode the request data from JSON into an associative array
    // Handle potential decoding errors and respond with an error message
    try {
        $data = json_decode($data, true);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["error" => "Invalid data format"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    // Create a new FileMaker instance using the provided authentication details
    $fm = new Filemaker($username, $password, $database, $layout);
    $id = $args["id"];

    // If there's an ID provided in the request
    if (isset($id)) {

        // If a record with that ID does not exist
        if ($fm->getRecordById($id) == null) {

            // If the force-add parameter is set and true
            // then add a new record with the provided data
            if (isset($params["force-add"]) && $params["force-add"] == "true") {
                $record = $fm->addRecord($data);
            } else {
                // If the force-add parameter is not true,
                // then return an error message
                $response->getBody()->write(json_encode(["error" => "Record not found"]));
                return $response->withStatus(404)->withHeader("Content-Type", "application/json");
            }
        } else {
            // If a record with the provided ID does exist
            // then update the record with the provided data
            $record = $fm->updateRecord($id, $data);
        }
    } else {
        // If there's no ID provided in the request then add a new record
        $record = $fm->addRecord($data);
    }

    // Return the modified or created record in the response body
    $response->getBody()->write(json_encode($record));
    return $response->withHeader('Content-Type', 'application/json');
})->setName("updateOrCreateRecord");


$app->run();
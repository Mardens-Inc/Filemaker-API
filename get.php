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

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With");

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

// Route for the root endpoint
// It returns a JavaScript file when accessed
$app->get("/", function (Request $request, Response $response, $args) {
    $params = $request->getQueryParams();
    if (isset($params["minified"])) {
        $response->getBody()->write(file_get_contents("js/Filemaker.min.js"));
        return $response->withStatus(200)->withHeader("Content-Type", "text/javascript");
    }
    if (isset($params["time"])) {
        $response->getBody()->write(filemtime("js/Filemaker.js") . "");
        return $response->withStatus(200)->withHeader("Content-Type", "text/plain");
    }
    $response->getBody()->write(file_get_contents("js/Filemaker.js"));
    return $response->withStatus(200)->withHeader("Content-Type", "text/javascript");
});

$app->get(".js", function (Request $request, Response $response, $args) {
    return $response->withStatus(301)->withHeader("Location", "/");
});

// Route for /js/minified endpoint
// It returns a minified JavaScript when accessed
$app->get("/js/minified", function (Request $request, Response $response, $args) {
    $response->getBody()->write(file_get_contents("js/Filemaker.min.js"));
    return $response->withStatus(200)->withHeader("Content-Type", "text/javascript");
});

// Route for /js/time endpoint
// It returns the last modified time of the JavaScript file
$app->get("/js/time", function (Request $request, Response $response, $args) {
    $response->getBody()->write(filemtime("js/Filemaker.js") . "");
    return $response->withStatus(200)->withHeader("Content-Type", "text/plain");
});

// Route for /databases endpoint
// It returns a list of databases if auth headers are valid, otherwise an error message
$app->get("/databases", function (Request $request, Response $response, $args) {
    $authentication = parseAuthHeaders($request);
    if (!$authentication) {
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }
    $username = $authentication["username"];
    $password = $authentication["password"];
    $databases = Filemaker::getDatabases($username, $password);
    $response->getBody()->write(json_encode($databases));
    return $response->withStatus(200)->withHeader("Content-Type", "application/json");
});

// Define a GET route for fetching layouts from specified database
$app->get("/databases/{database}/layouts", function (Request $request, Response $response, $args) {

    // Parse authentication headers from the request
    $authentication = parseAuthHeaders($request);

    // If authentication parsing fails, return an error in JSON format
    if (!$authentication) {
        // Writing error message to the response
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    // If authentication is valid, extract username and password
    $username = $authentication["username"];
    $password = $authentication["password"];

    // Extract database name from the dynamic route parameters
    $database = $args["database"];

    // Get list of layouts from the specified database
    $layouts = Filemaker::getLayouts($username, $password, $database);

    // Write the retrieved layouts to the response body in JSON format
    $response->getBody()->write(json_encode($layouts));

    // Return the response
    return $response->withStatus(200)->withHeader("Content-Type", "application/json");
});

// Starts a GET route
$app->get("/databases/{database}/layouts/{layout}/fields", function (Request $request, Response $response, $args) {
    // Parses the authentication headers to obtain 'username' and 'password'.
    // If the authentication is invalid, it returns an error message in JSON format.
    $authentication = parseAuthHeaders($request);
    if (!$authentication) {
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    // Extracts username and password from the parsed authentication
    $username = $authentication["username"];
    $password = $authentication["password"];

    // Extracts database and layout from the route parameters
    $database = $args["database"];
    $layout = $args["layout"];

    // Creates a new instance of the FileMaker class and stores it in the $fm variable
    $fm = new FileMaker($username, $password, $database, $layout);

    // Calls the getRowNames method on the $fm object and stores the result in the $fields variable
    $fields = $fm->getRowNames();

    // Writes the $fields array as a JSON string to the response's body
    $response->getBody()->write(json_encode($fields));

    // Returns the response
    return $response->withStatus(200)->withHeader("Content-Type", "application/json");
});

// This block of code sets up a route for getting records from a specified database and layout
$app->get("/databases/{database}/layouts/{layout}/records", function (Request $request, Response $response, $args) {

    // This is the authentication part.
    // It parses the authentication headers to obtain 'username' and 'password'
    // If invalid, it will return an error message in the response
    $authentication = parseAuthHeaders($request);
    if (!$authentication) {
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    // Extract username and password from the parsed authentication data
    $username = $authentication["username"];
    $password = $authentication["password"];

    // Fetch query parameters from the Request object
    $params = $request->getQueryParams();
    // Fetch path parameters from the Slim routing results
    $database = $args["database"];
    $layout = $args["layout"];

    // Create an instance of the FileMaker class with provided credentials and layout details
    $fm = new FileMaker($username, $password, $database, $layout);

    try {

        // If 'limit' param is set in the query, convert it to integer otherwise default it to 100
        if (isset($params["limit"])) {
            $limit = intval($params["limit"]);
        } else {
            $limit = 100;
        }
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["error" => "Invalid limit value", "message" => $e->getMessage()]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    try {

        // If 'offset' query param is set, convert it to integer. If it is less than or equal to 0, set it to 1
        // Otherwise, if 'offset' is not set, set it to 1
        if (isset($params["offset"])) {
            $offset = intval($params["offset"]);
            $offset = $offset <= 0 ? 1 : $offset;
        } else {
            $offset = 1;
        }
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["error" => "Invalid offset value", "message" => $e->getMessage()]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    // Fetch records using the FileMaker instance's 'getRecords' function
    $records = $fm->getRecords($offset, $limit);

    // Write the records to the response body as a JSON formatted string
    $response->getBody()->write(json_encode($records));

    // Return the completed Response object
    return $response->withStatus(200)->withHeader("Content-Type", "application/json");
});

$app->get("/databases/{database}/layouts/{layout}/records/all", function (Request $request, Response $response, $args) {
    $authentication = parseAuthHeaders($request);
    if (!$authentication) {
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }
    $username = $authentication["username"];
    $password = $authentication["password"];
    $database = $args["database"];
    $layout = $args["layout"];
    $fm = new FileMaker($username, $password, $database, $layout);
    $records = $fm->getAllRecords();
    $response->getBody()->write(json_encode($records));
    return $response->withStatus(200)->withHeader("Content-Type", "application/json");
});
$app->get("/databases/{database}/layouts/{layout}/records/count", function (Request $request, Response $response, $args) {
    $authentication = parseAuthHeaders($request);
    if (!$authentication) {
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }
    $username = $authentication["username"];
    $password = $authentication["password"];
    $database = $args["database"];
    $layout = $args["layout"];
    $fm = new FileMaker($username, $password, $database, $layout);
    $count = $fm->getNumberOfRecords();
    $response->getBody()->write(json_encode(["count" => $count]));
    return $response->withStatus(200)->withHeader("Content-Type", "application/json");
});

// Define a route for getting a specific record from a specific database and layout
$app->get("/databases/{database}/layouts/{layout}/records/{recordId}", function (Request $request, Response $response, $args) {
    // Parse the authentication headers from the request
    $authentication = parseAuthHeaders($request);

    // Check if the parsed authentication is valid
    if (!$authentication) {
        // Write the error response if authentication is invalid
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    // Extract the credentials from the parsed authentication
    $username = $authentication["username"];
    $password = $authentication["password"];

    // Extract the database and layout from the request parameters
    $database = $args["database"];
    $layout = $args["layout"];

    // Try to parse the recordID in an integer format
    try {
        $recordId = intval($args["recordId"]);
    } catch (Exception $e) {
        // If the recordId is not an integer, write an error and stop execution
        $response->getBody()->write(json_encode(["error" => "Invalid record id"]));
        header("Content-Type: application/json");
        return $response;
    }

    // Create a new FileMaker instance with the provided credentials, database, and layout
    $fm = new FileMaker($username, $password, $database, $layout);
    // Fetch the requested record from the database
    $record = $fm->getRecordByID($recordId);

    // Write the fetched record to the response body
    $response->getBody()->write(json_encode($record));
    // Set the response type to JSON
    header("Content-Type: application/json");
    // Return the response
    return $response;
});

// Define a route for getting active authentication sessions
$app->get("/auth/active", function (Request $request, Response $response, $args) {

    // Try to fetch the list of active sessions
    try {
        $list = FilemakerMemory::list();
        // Write the list of active sessions to the response body
        $response->getBody()->write(json_encode($list));
        // Return the response
        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    } catch (Exception $e) {
        // Write the error message to the response body
        $response->getBody()->write(json_encode(["error" => "Unable to fetch user list", "message" => $e->getMessage()]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }
});

$app->get("/databases/{database}/layouts/{layout}/search", function (Request $request, Response $response, $args) {


    $authentication = parseAuthHeaders($request);
    if (!$authentication) {
        $response->getBody()->write(json_encode(["error" => "Invalid authentication options"]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    $database = $args["database"];
    $layout = $args["layout"];
    $username = $authentication["username"];
    $password = $authentication["password"];
    $fm = new FileMaker($username, $password, $database, $layout);

    $params = $request->getQueryParams();
    $query = $params["query"] ?? "";
    $ascending = $params["ascending"] ?? true;
    $records = $fm->search($query, $ascending);

    $response->getBody()->write(json_encode($records));
    return $response->withStatus(200)->withHeader("Content-Type", "application/json");
});


$app->run();


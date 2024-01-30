<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === "GET" || $_SERVER['REQUEST_METHOD'] === "HEAD") {
    if (isset($_GET["time"])) {
        header("Content-Type: text/plaintext");
        http_response_code(200);
        print_r(filemtime("js/Filemaker.js"));
        die();
    }

    header("Content-Type: text/javascript");
    http_response_code(200);
    if (isset($_GET["minified"])) {
        die(file_get_contents("js/Filemakler.min.js"));
    }
    die(file_get_contents("js/Filemaker.js"));
}
 else if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    header('Access-Control-Allow-Headers: x-requested-with');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 604800');
    die();
} else {
    header("Content-Type: application/json");
    http_response_code(400);
    die(json_encode(["success" => false, "message" => "Invalid request."]));
}

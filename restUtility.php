<?php

use Psr\Http\Message\ServerRequestInterface as Request;
require_once './vendor/autoload.php';


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
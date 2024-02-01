<?php

namespace Filemaker;
require_once "FilemakerMemory.php";

/**
 * A class for interacting with the FileMaker Data API.
 * @author Drew Chase <drew.chase@mardens.com>
 * @version 0.0.1
 */
class FileMaker
{
    private string $database;
    private string|null $token;
    private string $table;
    const URL_BASE = "https://fm.mardens.com/fmi/data/vLatest";

    /**
     * Creates a new FileMaker object.
     * @param string $username The username for the FileMaker Data API.
     * @param string $password The password for the FileMaker Data API.
     * @param string $database The database name.
     * @param string $table The table name.
     * @return void The new FileMaker object.
     * @example $fm = new FileMaker('user', 'pass123', 'db_name', 'Inventory');
     */
    public function __construct(string $username, string $password, string $database, string $table)
    {
        // Encode the username and password as a base64 string this is used to get the session token
        // Set the database name
        $this->database = self::encodeParameter($database);
        // Set the table name
        $this->table = self::encodeParameter($table);
        // Initializes the token to null
        $this->token = null;

        FilemakerMemory::init();

        $this->token = self::getSessionToken($database, $username, $password);
        FilemakerMemory::save($database, $this->token);
    }

    /**
     * Gets a session token from the FileMaker Data API.
     */
    private static function getSessionToken(string $database, string $username, string $password): string
    {

        if (FilemakerMemory::has($database)) {
            $token = FilemakerMemory::get($database);
            if ($token != null && self::validateToken($database, $token)) {
                return $token;
            } else {
                FilemakerMemory::delete($database);
            }
        }


        // Define the URL for the FileMaker Data API endpoint
        $url = self::URL_BASE . "/databases/" . $database . "/sessions";

        // Encode the username and password as a base64 string
        $base64 = base64_encode($username . ":" . $password);

        // Create a stream context for the HTTP request
        $context = stream_context_create(array(
            'http' => array(
                // Set the HTTP method to POST
                'method' => 'POST',
                // Define the HTTP headers for the request
                'header' => "Authorization: Basic " . $base64 . "\r\n" .
                    "User-Agent: PHP\r\n" .
                    "Content-Type: application/json\r\n",
                // Set the HTTP body content to an empty JSON object
                'content' => "{}",
                // Ignore HTTP errors and continue to get the content
                'ignore_errors' => true,
            ),
            'ssl' => array(
                // Disable SSL peer and host verification
                'verify_peer' => false,
                'verify_peer_name' => false,
            ),
        ));

        // Send the HTTP request and get the response content
        $result = file_get_contents($url, false, $context);

        // If the result is FALSE, an error occurred
        if ($result === FALSE) {
            // Output an error message
            return "";
        }

        // Decode the JSON response into an associative array
        $resultArray = json_decode($result, true);

        // Extract the token from the response array
        return $resultArray['response']['token'];
    }


    /**
     * Gets the records from the database.
     * @param int $start The starting record number. (This value is 1-based.)
     * @param int $limit The number of records to get.
     * @return array The records.
     */
    public function getRecords(int $start = 1, int $limit = 10): array
    {
        // Define the URL for the FileMaker Data API endpoint, including the database name and layout name.
        // The _offset and _limit query parameters are used for pagination.
        $url = self::URL_BASE . "/databases/" . $this->database . "/layouts/$this->table/records?_offset=" . $start . "&_limit=" . $limit;

        // Create a stream context for the HTTP request.
        return $this->getAuthenticatedStreamResponse($url, "GET")["response"]["data"];
    }

    /**
     * Returns the names of fields in the given record excluding the ones starting with 'g_' (global fields)
     *
     * @param array $record An example record with 'fieldData' element containing field names as keys
     * @return array An array of field names
     */
    public function getRowNamesByExample(array $record): array
    {
        $fields = [];
        foreach (array_keys($record['fieldData']) as $field) {
            if (!str_starts_with($field, "g_")) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    /**
     * Gets the field names for the first record.
     */
    public function getRowNames(): array
    {
        $records = $this->getRecords();
        if (count($records) > 0) {
            return $this->getRowNamesByExample($records[0]);
        }
        return [];
    }

    /**
     * Searches the database for records matching the specified query.
     * @param array $fields The query.
     * @param array $sort The sort order.
     * @param bool $ascending Whether to sort in ascending order.
     * @return array The matching records.
     */
    public function advancedSearch(array $fields, array $sort = [], bool $ascending = true): array
    {
        // Define the URL for the FileMaker Data API endpoint, including the database name and layout name.
        $url = self::URL_BASE . "/databases/" . $this->database . "/layouts/$this->table/_find";

        // Set the HTTP body content to a JSON object containing the query and sort parameters.
        $content = [];
        $content["query"] = $fields;
        foreach ($sort as $s) {
            $content["sort"] += ["fieldName" => $s, "sortOrder" => $ascending ? "ascend" : "descend"];
        }
//        die(json_encode($content));
        // Create a stream context for the HTTP request.
        $result = $this->getAuthenticatedStreamResponse($url, "POST", json_encode($content));

        // Return the 'data' array from the response.
        $result = @$result['response']['data'];
        if ($result == null) return [];

        // Return the 'data' array from the response.
        return $result;
    }

    public function search(string $query, bool $ascending = true): array
    {
        $rows = $this->getRowNames();
        $fields = [];
        foreach ($rows as $row) {
            $fields[] = [$row => $query];
        }
        return $this->advancedSearch($fields, [], $ascending);
    }

    /**
     * Updates a record in the database.
     * @param int $id The ID of the record to update.
     * @param array $fieldData The field data to update.
     * @return array The updated record.
     */
    public function updateRecord(int $id, array $fieldData): array
    {
        // Define the URL for the FileMaker Data API endpoint, including the database name, layout name, and record ID.
        $url = self::URL_BASE . "/databases/" . $this->database . "/layouts/$this->table/records/$id";
        $result = $this->getAuthenticatedStreamResponse($url, "PATCH", json_encode(["fieldData" => $fieldData]));
        $record = $this->getRecordById($id);

        // Return the 'data' array from the response.
        return ["result" => $result, "record" => $record];
    }

    /**
     * Adds a record to the database.
     * @param array $fieldData The field data for the new record.
     * @return array The added record.
     */
    public function addRecord(array $fieldData): array
    {
        // Define the URL for the FileMaker Data API endpoint, including the database name and layout name.
        $url = self::URL_BASE . "/databases/" . $this->database . "/layouts/$this->table/records/";

        $result = $this->getAuthenticatedStreamResponse($url, "POST", json_encode(["fieldData" => $fieldData]));

        $recordId = $result["response"]["recordId"];
        $result = $this->getRecordById($recordId);

        return ["success" => true, "result" => $result];
    }

    /**
     * Gets a record from the database.
     * @param int $id The ID of the record to get.
     * @return array The record.
     */
    public function getRecordByID(int $id): array
    {
        // Define the URL for the FileMaker Data API endpoint, including the database name, layout name, and record ID.
        $url = self::URL_BASE . "/databases/" . $this->database . "/layouts/$this->table/records/$id";

        // Create a stream context for the HTTP request.
        $result = $this->getAuthenticatedStreamResponse($url, "GET");
        if(count($result["response"]) == 0) return [];
        // Return the 'data' array from the response.
        return $result["response"]["data"][0];
    }

    /**
     * Deletes a record from the database.
     * @param int $id The ID of the record to delete.
     * @return array The deleted record.
     */
    public function deleteRecord(int $id): array
    {
        // Define the URL for the FileMaker Data API endpoint, including the database name, layout name, and record ID.
        $url = self::URL_BASE . "/databases/" . $this->database . "/layouts/$this->table/records/$id";

        $response = $this->getAuthenticatedStreamResponse($url, "DELETE");
        if ($response == []) return [];

        // Return the 'data' array from the response.
        return ["success" => true];
    }

    /**
     * Deletes the specified database.
     *
     * @param string $database The name of the database to be deleted.
     * @param string $username The username for authentication.
     * @param string $password The password for authentication.
     * @return void
     */
    public static function deleteDatabase(string $database, string $username, string $password): void
    {
        $url = self::URL_BASE . "/databases/" . self::encodeParameter($database);
        self::getStreamResponse($url, "DELETE", "Bearer " . self::getSessionToken($database, $username, $password));
    }

    /**
     * Deletes all records from the database.
     */
    public function clearDatabase(): void
    {
        $records = $this->getRecords();
        foreach ($records as $record) {
            $id = $record['recordId'];
            $this->deleteRecord($id);
        }
    }

    /**
     * Creates a stream context for authenticated HTTP requests.
     *
     * @param string $url The URL of the endpoint.
     * @param string $method The HTTP method.
     * @param string $content The HTTP body content. (default: "{}")
     *
     * @return mixed The response of the HTTP request.
     */
    private function getAuthenticatedStreamResponse(string $url, string $method, string $content = "{}"): mixed
    {
        return self::getStreamResponse($url, $method, "Bearer " . $this->token, $content);
    }

    /**
     * Creates a stream context for the HTTP request.
     *
     * @param string $url The URL to send the request to.
     * @param string $method The HTTP method to use (e.g., GET, POST).
     * @return mixed The response data.
     */
    private static function getStreamResponse(string $url, string $method, string $authorization, string $content = "{}"): mixed
    {
        $url = self::encodeParameter($url);
        $context = stream_context_create(array(
            'http' => array(
                // Set the HTTP method to GET.
                'method' => $method,
                // Define the HTTP headers for the request, including the authorization token.
                'header' => "Authorization: " . $authorization . "\r\n" .
                    "User-Agent: PHP\r\n" .
                    "Content-Type: application/json\r\n",
                // Set the HTTP body content to an empty JSON object.
                'content' => $content,
                // Ignore HTTP errors and continue to get the content.
                'ignore_errors' => true,
            ),
            'ssl' => array(
                // Disable SSL peer and host verification.
                'verify_peer' => false,
                'verify_peer_name' => false,
            ),
        ));

        // Send the HTTP request and get the response content.
        $result = file_get_contents($url, false, $context);

        // If the result is FALSE, an error occurred.
        if ($result === FALSE) {
            // Return an empty array.
            return [];
        }
        // Return the 'data' array from the response.
        return json_decode($result, true);
    }


    /**
     * Retrieves the list of databases accessible to the specified user.
     *
     * @param string $username The username.
     * @param string $password The password.
     * @return array The list of accessible databases.
     */
    public static function getDatabases(string $username, string $password): array
    {
        $url = self::URL_BASE . "/databases";
        $result = self::getStreamResponse($url, "GET", "Basic " . base64_encode($username . ":" . $password));
        $result = $result["response"]["databases"];

        // map the databases to an array of names
        return array_map(function ($database) {
            return $database["name"];
        }, $result);
    }

    public static function getLayouts(string $username, string $password, string $database): array
    {
        $database = self::encodeParameter($database);
        $url = self::URL_BASE . "/databases/" . $database . "/layouts/";
        $result = self::getStreamResponse($url, "GET", "Bearer " . self::getSessionToken($database, $username, $password));
        $result = $result["response"]["layouts"];

        // map the databases to an array of names
        return array_map(function ($layout) {
            return $layout["name"];
        }, $result);
    }


    /**
     * Validates the provided token against the specified database.
     *
     * @param string $database The name of the database.
     * @param string $token The token to be validated.
     *
     * @return bool Returns true if the token is valid, false otherwise.
     */
    public static function validateToken(string $database, string $token): bool
    {
        $database = self::encodeParameter($database);

        $url = self::URL_BASE . "/databases/" . $database . "/layouts/";
        $result = self::getStreamResponse($url, "GET", "Bearer " . $token);

        return intval($result["messages"][0]["code"]) !== 952;
    }

    /**
     * Encodes a parameter string by replacing spaces with "%20".
     * @param string $parameter The parameter string to encode.
     * @return string The encoded parameter string.
     */
    private static function encodeParameter(string $parameter): string
    {
        return str_replace(" ", "%20", $parameter);
    }
}

<?php

namespace Filemaker;
/**
 * A class for interacting with the FileMaker Data API.
 * @author Drew Chase <drew.chase@mardens.com>
 * @version 0.0.1
 */
class FileMaker
{
    private string $database;
    private string $token;
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
        $this->database = $database;
        // Set the table name
        $this->table = $table;
        // Initializes the token to null
        $this->token = null;


        // Check if the token is saved in memory
        if (FilemakerMemory::getInstance()->has($database)) {
            // Set the token to the saved token
            $this->token = FilemakerMemory::getInstance()->get($database);
        }
        if ($this->token == null || !$this->validateToken($this->token)) {
            // Get the session token
            $this->token = $this->getSessionToken(base64_encode($username . ":" . $password));
            FilemakerMemory::getInstance()->save($database, $this->token);
        }
    }

    /**
     * Gets a session token from the FileMaker Data API.
     */
    private function getSessionToken(string $base64): string
    {
        // Define the URL for the FileMaker Data API endpoint
        $url = self::URL_BASE . "/databases/" . $this->database . "/sessions";

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
            echo 'Error: Unable to get content';
            // End the function
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
     * @param int $offset The number of records to get.
     * @return array The records.
     */
    public function getRecords(int $start = 1, int $offset = 10): array
    {
        // Define the URL for the FileMaker Data API endpoint, including the database name and layout name.
        // The _offset and _limit query parameters are used for pagination.
        $url = self::URL_BASE . "/databases/" . $this->database . "/layouts/$this->table/records?_offset=" . $start . "&_limit=" . $offset;

        // Create a stream context for the HTTP request.
        return $this->createAStreamContextForTheHTTPRequest($url, "GET");
    }

    /**
     * Gets the field names for the specified record.
     * @param array $record A record example.
     * @return array The field names.
     */
    public function getRowNamesByExample(array $record): array
    {
        // Return the 'data' array from the response.
        return array_keys($record['fieldData']);
    }

    /**
     * Gets the field names for the first record.
     */
    public function getRowNames(): array
    {
        return $this->getRowNamesByExample($this->getRecords()[0]);
    }

    /**
     * Searches the database for records matching the specified query.
     * @param array $fields The query.
     * @param array $sort The sort order.
     * @param bool $ascending Whether to sort in ascending order.
     * @return array The matching records.
     */
    public function Search(array $fields, array $sort = [], bool $ascending = true): array
    {
        // Define the URL for the FileMaker Data API endpoint, including the database name and layout name.
        $url = "https://fm.mardens.com/fmi/data/vLatest/databases/" . $this->database . "/layouts/$this->table/_find";

        // Set the HTTP body content to a JSON object containing the query and sort parameters.
        $content = [];
        $content["query"] = [$fields];
        foreach ($sort as $sort) {
            $content["sort"] += ["fieldName" => $sort, "sortOrder" => $ascending ? "ascend" : "descend"];
        }

        // Create a stream context for the HTTP request.
        $result = $this->createAStreamContextForTheHTTPRequest($url, "POST", json_encode($content));

        // Return the 'data' array from the response.
        $result = $result['response']['data'];
        if ($result == null) return [];

        // Return the 'data' array from the response.
        return $result;
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
        $url = "https://fm.mardens.com/fmi/data/vLatest/databases/" . $this->database . "/layouts/$this->table/records/$id";
        $result = $this->createAStreamContextForTheHTTPRequest($url, "PATCH", json_encode(["fieldData" => $fieldData]));
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
        if (count($this->Search($fieldData)) > 0) {
            $record = $this->Search($fieldData)[0]["recordId"];
            return $this->updateRecord($record['recordId'], $fieldData);
        }
        // Define the URL for the FileMaker Data API endpoint, including the database name and layout name.
        $url = self::URL_BASE . "/databases/" . $this->database . "/layouts/$this->table/records/";

        $result = $this->createAStreamContextForTheHTTPRequest($url, "POST", json_encode(["fieldData" => $fieldData]));

        $recordId = $result["response"]["recordId"];
        $result = $this->getRecordById($recordId);

        return ["success" => true, "result" => $result];
    }

    /**
     * Gets a record from the database.
     * @param int $id The ID of the record to get.
     * @return array The record.
     */
    public function getRecordByID($id)
    {
        // Define the URL for the FileMaker Data API endpoint, including the database name, layout name, and record ID.
        $url = "https://fm.mardens.com/fmi/data/vLatest/databases/" . $this->database . "/layouts/$this->table/records/$id";

        // Create a stream context for the HTTP request.
        return $this->createAStreamContextForTheHTTPRequest($url);
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

        $response = $this->createAStreamContextForTheHTTPRequest($url, "DELETE");
        if ($response == []) return [];

        // Return the 'data' array from the response.
        return ["success" => true];
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
     * Creates a stream context for the HTTP request.
     *
     * @param string $url The URL to send the request to.
     * @param string $method The HTTP method to use (e.g., GET, POST).
     * @return mixed The response data.
     */
    private function createAStreamContextForTheHTTPRequest(string $url, string $method, string $content = "{}"): mixed
    {
        $context = stream_context_create(array(
            'http' => array(
                // Set the HTTP method to GET.
                'method' => $method,
                // Define the HTTP headers for the request, including the authorization token.
                'header' => "Authorization: Bearer " . $this->token . "\r\n" .
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
            // Output an error message.
            echo 'Error: Unable to get content';
            // Return an empty array.
            return [];
        }

        // Return the 'data' array from the response.
        return json_decode($result, true);
    }


    private function validateToken(string $token)
    {
        $url = self::URL_BASE . "/databases/" . $this->database . "/layouts/";
        $result = $this->createAStreamContextForTheHTTPRequest($url, "GET");
        return $result["messages"][0]["message"] != "Invalid FileMaker Data API token (*)";
    }
}

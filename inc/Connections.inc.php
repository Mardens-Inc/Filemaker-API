<?php

namespace Filemaker;

require_once realpath("./vendor/autoload.php");

use mysqli;

class Connections
{
    /**
     * Returns a connection to the FileMaker database.
     *
     * @return mysqli to the mysql database.
     */
    public static function getConnection(): mysqli
    {
        $config = parse_ini_file(realpath("./.env"));
        $host = $config["DB_HOST"];
        $database = $config["DB_DATABASE"];
        $username = $config["DB_USERNAME"];
        $password = $config["DB_PASSWORD"];

        try {
            $connection = new mysqli($host, $username, $password);

            // Create database if it does not exist
            $connection->query("CREATE DATABASE IF NOT EXISTS $database");

            // Select the database
            $connection->select_db($database);
            return $connection;
        } catch (\Exception $e) {
            http_response_code(500);
            die(json_encode(["error" => "Could not connect to database.", "message" => $e->getMessage()]));
        }

    }

}
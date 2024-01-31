<?php

namespace Filemaker;

require_once "Connections.inc.php";

/**
 * Class FilemakerMemory
 *
 * This class represents a memory object for storing database tokens.
 * It is implemented as a singleton, ensuring that only one instance
 * of this class is created throughout the application.
 */
class FilemakerMemory
{

    /**
     * Initializes the memory table in the database.
     */
    public static function init(): void
    {
        $conn = Connections::getConnection();
        $stmt = $conn->prepare("CREATE TABLE IF NOT EXISTS `memory` (`name` VARCHAR(255) PRIMARY KEY, `token` VARCHAR(4096))");
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    /**
     * @param string $database The database to save the token for.
     * @param string $token The token to save.
     * @return void
     */
    public static function save(string $database, string $token): void
    {
        $conn = Connections::getConnection();
        $stmt = $conn->prepare("INSERT INTO `memory` (`name`, `token`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `token` = ?");
        $stmt->bind_param("sss", $database, $token, $token);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }


    /**
     * Returns the token for the given database.
     * @param string $database The database to retrieve the token for.
     * @return string|null The token for the given database, or null if the token could not be found.
     */
    public static function get(string $database): ?string
    {
        $conn = Connections::getConnection();
        $stmt = $conn->prepare("SELECT `token` FROM `memory` WHERE name = ?");
        $stmt->bind_param("s", $database);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row["token"];
        }

        return null;
    }

    /**
     * Returns whether the given database has a token saved.
     * @param string $database The database to check.
     * @return bool Whether the given database has a token saved.
     */
    public static function has(string $database): bool
    {
        $conn = Connections::getConnection();
        $stmt = $conn->prepare("SELECT * FROM `memory` WHERE name = ?");
        $stmt->bind_param("s", $database);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        return $result->num_rows > 0;
    }

    /**
     * Deletes the token for the given database.
     * @param string $database The database to delete the token for.
     * @return void
     */
    public static function delete(string $database): void
    {
        $conn = Connections::getConnection();
        $stmt = $conn->prepare("DELETE FROM `memory` WHERE name = ?");
        $stmt->bind_param("s", $database);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    public static function list(): array
    {
        $conn = Connections::getConnection();
        $stmt = $conn->prepare("SELECT * FROM `memory`");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        $databases = [];
        while ($row = $result->fetch_assoc()) {
            $databases[] = $row["name"];
        }
        return $databases;
    }


}
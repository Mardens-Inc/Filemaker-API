<?php


namespace Filemaker;
/**
 * Class FilemakerMemory
 *
 * This class represents a memory object for storing database tokens.
 * It is implemented as a singleton, ensuring that only one instance
 * of this class is created throughout the application.
 */
class FilemakerMemory
{
    private static FilemakerMemory $instance;
    private array $memory;

    protected function __construct()
    {
        $this->memory = [];
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return FilemakerMemory The singleton instance of this class.
     */
    public static function getInstance(): FilemakerMemory
    {
        if (!isset(self::$instance)) {
            self::$instance = new FilemakerMemory();
        }
        return self::$instance;
    }

    /**
     * @param string $database The database to save the token for.
     * @param string $token The token to save.
     * @return void
     */
    public function save(string $database, string $token): void
    {
        $this->memory[$database] = $token;
    }


    /**
     * Returns the token for the given database.
     * @param string $database The database to retrieve the token for.
     * @return string|null The token for the given database, or null if the token could not be found.
     */
    public function get(string $database): ?string
    {
        return $this->memory[$database] ?? null;
    }

    /**
     * Returns whether the given database has a token saved.
     * @param string $database The database to check.
     * @return bool Whether the given database has a token saved.
     */
    public function has(string $database): bool
    {
        return isset($this->memory[$database]);
    }

    /**
     * Deletes the token for the given database.
     * @param string $database The database to delete the token for.
     * @return void
     */
    public function delete(string $database): void
    {
        if ($this->has($database)) {
            unset($this->memory[$database]);
        }
    }

    public function list(): array
    {
        return $this->memory;
    }


}
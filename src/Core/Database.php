<?php

namespace App\Core;

use App\Config\DatabaseConfig;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private DatabaseConfig $config;

    private function __construct(DatabaseConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Get the PDO instance. Implements Singleton pattern for the database connection.
     * @param DatabaseConfig $config
     * @return PDO
     * @throws PDOException
     */
    public static function getInstance(DatabaseConfig $config): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host={$config->host};dbname={$config->name};charset={$config->charset}";
                self::$instance = new PDO($dsn, $config->user, $config->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch results as associative arrays by default
                    PDO::ATTR_EMULATE_PREPARES => false, // Disable emulation for better security and performance
                ]);
            } catch (PDOException $e) {
                // In a real application, log this error and show a generic message.
                // For development, we can display it.
                error_log("Database connection failed: " . $e->getMessage());
                die("Database connection failed. Please check logs for details.");
            }
        }
        return self::$instance;
    }
}
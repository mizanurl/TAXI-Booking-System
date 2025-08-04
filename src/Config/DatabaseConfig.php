<?php

namespace App\Config;

class DatabaseConfig
{
    public readonly string $host;
    public readonly string $name;
    public readonly string $user;
    public readonly string $password;
    public readonly string $charset;

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->name = $_ENV['DB_NAME'] ?? 'taxi_booking_db';
        $this->user = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
        $this->charset = 'utf8mb4'; // Standard for modern applications
    }
}
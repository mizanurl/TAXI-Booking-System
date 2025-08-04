<?php

// Ensure Composer's autoloader is included first
require_once __DIR__ . '/vendor/autoload.php';

// Use statements for all classes used in cli.php
use App\Config\DatabaseConfig;
use App\Core\Database;
use App\Database\Migrator;
use App\Database\Seeder;

// Load environment variables using Dotenv
// This makes sure $_ENV variables are populated from your .env file
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();
}

// Instantiate DatabaseConfig
$dbConfig = new DatabaseConfig();

// Get the PDO database connection instance
$pdo = Database::getInstance($dbConfig);

// Get the command from command line arguments
$command = $argv[1] ?? null;
$arg2 = $argv[2] ?? null; // Capture the second argument (e.g., seeder name)

// Handle different CLI commands
switch ($command) {
    case 'migrate':
        $migrator = new Migrator($pdo);
        $migrator->runMigrations();
        break;
    case 'seed':
        $seeder = new Seeder($pdo);
        // Pass the second argument (seeder name) to runSeeders
        $seeder->runSeeders($arg2);
        break;
    case 'make:migration':
        $migrationName = $arg2;
        if (!$migrationName) {
            echo "Usage: php cli.php make:migration <MigrationName>\n";
            exit(1);
        }
        $migrator = new Migrator($pdo);
        $migrator->createMigrationFile($migrationName);
        break;
    case 'make:seeder':
        $seederName = $arg2;
        if (!$seederName) {
            echo "Usage: php cli.php make:seeder <SeederName>\n";
            exit(1);
        }
        $seeder = new Seeder($pdo);
        $seeder->createSeederFile($seederName);
        break;
    default:
        echo "Available commands:\n";
        echo "  migrate           Run database migrations.\n";
        echo "  seed              Run all database seeders.\n";
        echo "  seed <SeederName> Run a specific database seeder (e.g., AirportSeeder).\n";
        echo "  make:migration <name> Create a new migration file.\n";
        echo "  make:seeder <name>    Create a new seeder file.\n";
        break;
}
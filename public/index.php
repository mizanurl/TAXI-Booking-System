<?php

// public/index.php - Main application entry point for Apache.
// CORS headers and static file serving are handled by Apache and .htaccess.

// Ensure Composer's autoloader is included. This handles loading all your classes.
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from the .env file.
// This is crucial for accessing database credentials, API keys, etc.
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Enable session support.
session_start();

// Use statements for core application components.
use App\Core\App;
use App\Core\Router;
use App\Config\DatabaseConfig;
use App\Config\AppConfig;

// IMPORTANT: No direct 'header()' calls for CORS here.
// IMPORTANT: No 'Request' or 'Response' imports for early CORS handling here.
// IMPORTANT: Do NOT add CorsMiddleware here if its only purpose is CORS.
// If your CorsMiddleware has other non-CORS responsibilities, you can add it back here.
// Example: $router->addMiddleware(new App\Http\Middleware\CorsMiddleware());

// Initialize application configuration classes.
$appConfig = new AppConfig();
$dbConfig = new DatabaseConfig();

// Initialize the Router. This is where your API routes will be defined and processed.
$router = new Router();

// Create the main application instance and run it.
// The App class will handle dependency injection, route resolution, and request dispatching.
$app = new App($router, $dbConfig, $appConfig);
$app->run();
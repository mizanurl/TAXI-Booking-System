<?php

// --- IMPORTANT: Suppress all error reporting for clean JSON output ---
error_reporting(0); // Turn off all error reporting
ini_set('display_errors', 0); // Do not display errors to the screen
ini_set('display_startup_errors', 0); // Do not display startup errors
// ---------------------------------------------------------------------

// Ensure Composer's autoloader is included
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    exit(1);
}
require_once __DIR__ . '/vendor/autoload.php';

// Paths to your API source code where annotations are defined
$sourcePaths = [
    __DIR__ . '/src/Controllers',
    __DIR__ . '/src/Models',
    __DIR__ . '/src/Http/Requests',
    __DIR__ . '/src/OpenApi', // <--- NEW: Include the directory with your global OpenAPI definition
    // __DIR__ . '/src/Http/Requests/Airport', // Keep commented out if it causes duplication
];

try {
    $generator = new \OpenApi\Generator();
    $openApi = $generator->generate($sourcePaths);

    $jsonOutput = $openApi->toJson();

    $outputFilePath = __DIR__ . '/public/openapi.json';
    if (file_put_contents($outputFilePath, $jsonOutput) === false) {
        throw new \Exception("Failed to write JSON to file: {$outputFilePath}. Check directory permissions.");
    }

} catch (\Throwable $e) { // Catch Throwable to get all errors/exceptions
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    // For debugging in console, you might want to uncomment these lines:
    // echo "--- GENERATION ERROR ---\n";
    // echo "Error: " . $e->getMessage() . "\n";
    // echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    // echo "Trace:\n" . $e->getTraceAsString() . "\n";
    // echo "--- END GENERATION ERROR ---\n";
    exit(1);
}
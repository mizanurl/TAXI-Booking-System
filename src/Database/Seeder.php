<?php

namespace App\Database;

use PDO;
use PDOException;

class Seeder
{
    private PDO $db;
    private string $seedersPath;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->seedersPath = __DIR__ . '/Seeders';
    }

    /**
     * Run all or a specific seeder.
     * @param string|null $specificSeederName If provided, only this seeder will be run (e.g., 'AirportSeeder').
     */
    public function runSeeders(?string $specificSeederName = null): void
    {
        echo "Running seeders...\n";
        $files = scandir($this->seedersPath);
        $seedersToProcess = [];

        foreach ($files as $file) {
            if (str_ends_with($file, 'Seeder.php')) {
                $className = str_replace('.php', '', $file);
                // If a specific seeder name is provided, only consider that one
                if ($specificSeederName === null || $className === $specificSeederName) {
                    $seedersToProcess[] = $file;
                }
            }
        }

        if (empty($seedersToProcess)) {
            if ($specificSeederName) {
                echo "No seeder found with the name '{$specificSeederName}'.\n";
            } else {
                echo "No seeders found.\n";
            }
            return;
        }

        sort($seedersToProcess); // Run in alphabetical order for consistency

        foreach ($seedersToProcess as $file) {
            // Construct the full class name with namespace
            $className = str_replace('.php', '', $file);
            $fullClassName = 'App\\Database\\Seeders\\' . $className;

            // Only require and instantiate if the class exists and matches our criteria
            if (class_exists($fullClassName)) {
                // If a specific seeder was requested, and this isn't it, skip.
                // This check is redundant if $seedersToProcess was filtered correctly,
                // but adds an extra layer of safety.
                if ($specificSeederName !== null && $className !== $specificSeederName) {
                    continue; // Skip to the next file if it's not the one we want
                }

                require_once $this->seedersPath . '/' . $file; // Only load the file when needed

                $seeder = new $fullClassName($this->db);
                if (method_exists($seeder, 'run')) {
                    try {
                        echo "Seeding: {$file}\n";
                        $seeder->run();
                        echo "Seeded: {$file}\n";
                    } catch (PDOException $e) {
                        error_log("Seeder failed for {$file}: " . $e->getMessage());
                        echo "Seeder FAILED for {$file}: " . $e->getMessage() . "\n";
                        die("Seeding process aborted.");
                    }
                }
            } else {
                error_log("Seeder class not found: {$fullClassName} for file {$file}");
                echo "Error: Seeder class {$fullClassName} not found for file {$file}.\n";
            }
        }
        echo "Seeding complete.\n";
    }

    /**
     * Creates a new seeder file.
     * @param string $name
     */
    public function createSeederFile(string $name): void
    {
        $fileName = $name . '.php';
        $className = $name;
        $filePath = $this->seedersPath . '/' . $fileName;

        $stub = <<<EOT
<?php

namespace App\Database\Seeders;

use PDO;

class {$className}
{
    private PDO \$db;

    public function __construct(PDO \$db)
    {
        \$this->db = \$db;
    }

    public function run(): void
    {
        // Example: Seed initial data
        // \$stmt = \$this->db->prepare("INSERT INTO your_table_name (column1, column2) VALUES (?, ?)");
        // \$stmt->execute(['value1', 123]);
        // echo "Seeded initial data for your_table_name.\n";
    }
}
EOT;

        if (!is_dir($this->seedersPath)) {
            mkdir($this->seedersPath, 0777, true);
        }

        file_put_contents($filePath, $stub);
        echo "Created seeder: {$fileName}\n";
    }
}
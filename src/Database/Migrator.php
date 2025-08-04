<?php

namespace App\Database;

use PDO;
use PDOException;

class Migrator
{
    private PDO $db;
    private string $migrationsPath;
    private string $migrationTable = 'migrations';

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->migrationsPath = __DIR__ . '/Migrations';
        $this->ensureMigrationsTableExists();
    }

    /**
     * Ensures the migrations table exists.
     */
    private function ensureMigrationsTableExists(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Failed to create migrations table: " . $e->getMessage());
            die("Migration setup failed. Check logs.");
        }
    }

    /**
     * Get a list of applied migrations.
     * @return array
     */
    private function getAppliedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationTable}");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Run all pending migrations.
     */
    public function runMigrations(): void
    {
        echo "Running migrations...\n";
        $appliedMigrations = $this->getAppliedMigrations();
        $files = scandir($this->migrationsPath);
        $pendingMigrations = [];

        foreach ($files as $file) {
            // Check for migration file pattern (e.g., 2023_10_27_123456_create_users_table.php)
            if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6}_)(.+)\.php$/', $file, $matches)) {
                $migrationNameWithTimestamp = str_replace('.php', '', $file);
                if (!in_array($migrationNameWithTimestamp, $appliedMigrations)) {
                    $pendingMigrations[] = $file;
                }
            }
        }

        if (empty($pendingMigrations)) {
            echo "No pending migrations.\n";
            return;
        }

        sort($pendingMigrations); // Ensure migrations run in chronological order
        $batch = $this->getCurrentBatch() + 1;

        foreach ($pendingMigrations as $file) {
            require_once $this->migrationsPath . '/' . $file;
            // Extract the class name from the filename (e.g., CreateUsersTable from 2023_10_27_..._create_users_table.php)
            $className = str_replace('.php', '', $file);
            // Remove timestamp prefix to get actual class name
            $className = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $className);
            $className = str_replace('_', '', ucwords($className, '_')); // Convert to PascalCase

            // Add the full namespace for the migration class
            $fullClassName = 'App\\Database\\Migrations\\' . $className;

            if (class_exists($fullClassName)) {
                $migration = new $fullClassName($this->db); // Pass PDO to migration
                if (method_exists($migration, 'up')) {
                    try {
                        echo "Migrating: {$file}\n";
                        $migration->up();
                        $stmt = $this->db->prepare("INSERT INTO {$this->migrationTable} (migration, batch) VALUES (:migration, :batch)");
                        $stmt->execute([':migration' => str_replace('.php', '', $file), ':batch' => $batch]);
                        echo "Migrated: {$file}\n";
                    } catch (PDOException $e) {
                        error_log("Migration failed for {$file}: " . $e->getMessage());
                        echo "Migration FAILED for {$file}: " . $e->getMessage() . "\n";
                        die("Migration process aborted.");
                    }
                }
            } else {
                error_log("Migration class not found: {$fullClassName} for file {$file}");
                echo "Error: Migration class {$fullClassName} not found for file {$file}.\n";
            }
        }
        echo "Migrations complete.\n";
    }

    /**
     * Get the current highest batch number.
     * @return int
     */
    private function getCurrentBatch(): int
    {
        $stmt = $this->db->query("SELECT MAX(batch) FROM {$this->migrationTable}");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Creates a new migration file.
     * @param string $name
     */
    public function createMigrationFile(string $name): void
    {
        $timestamp = date('Y_m_d_His');
        $fileName = $timestamp . '_' . strtolower($name) . '.php';
        $className = str_replace('_', '', ucwords($name, '_')); // Convert to PascalCase
        $filePath = $this->migrationsPath . '/' . $fileName;

        $stub = <<<EOT
<?php

namespace App\Database\Migrations;

use PDO;

class {$className}
{
    private PDO \$db;

    public function __construct(PDO \$db)
    {
        \$this->db = \$db;
    }

    public function up(): void
    {
        // SQL to create or alter tables
        // Example:
        // \$sql = "CREATE TABLE IF NOT EXISTS your_table_name (
        //     id INT AUTO_INCREMENT PRIMARY KEY,
        //     column1 VARCHAR(255) NOT NULL,
        //     column2 INT DEFAULT 0,
        //     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        // );";
        // \$this->db->exec(\$sql);
        // echo "Table 'your_table_name' created.\n";
    }

    public function down(): void
    {
        // SQL to revert changes (drop tables)
        // Example:
        // \$sql = "DROP TABLE IF EXISTS your_table_name;";
        // \$this->db->exec(\$sql);
        // echo "Table 'your_table_name' dropped.\n";
    }
}
EOT;

        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0777, true);
        }

        file_put_contents($filePath, $stub);
        echo "Created migration: {$fileName}\n";
    }
}
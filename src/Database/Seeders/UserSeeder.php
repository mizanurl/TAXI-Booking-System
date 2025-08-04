<?php

namespace App\Database\Seeders;

use PDO;
use App\Services\AuthService;
use App\Repositories\MySQL\UserDatabase;

class UserSeeder
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        // Check if users table is empty to prevent duplicate seeding
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() > 0) {
            echo "users table is not empty. Skipping UserSeeder.\n";
            return;
        }

        // Instantiate UserRepository and AuthService
        $userRepository = new UserDatabase($this->db);
        $authService = new AuthService($userRepository);

        $name = 'Admin User';
        $email = 'admin@example.com';
        $password = 'admin2025';
        $status = 1;

        try {
            $authService->registerUser($name, $email, $password, $status);
            echo "Default admin user '{$email}' created.\n";
        } catch (\Exception $e) {
            error_log("Failed to seed default admin user: " . $e->getMessage());
            echo "Failed to seed default admin user: " . $e->getMessage() . "\n";
        }
    }
}
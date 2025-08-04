<?php

namespace App\Database\Migrations;

use PDO;

class CreateUsersTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'users' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS users;";
        $this->db->exec($sql);
        echo "Table 'users' dropped.\n";
    }
}
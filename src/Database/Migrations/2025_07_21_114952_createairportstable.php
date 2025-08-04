<?php

namespace App\Database\Migrations;

use PDO;

class CreateAirportsTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS airports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT NULL,
            from_tax_toll DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            to_tax_toll DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'airports' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS airports;";
        $this->db->exec($sql);
        echo "Table 'airports' dropped.\n";
    }
}
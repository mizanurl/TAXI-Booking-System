<?php

namespace App\Database\Migrations;

use PDO;

class CreateExtraChargesTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS extra_charges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            area_name VARCHAR(100) NOT NULL,
            zip_codes TEXT NOT NULL,
            extra_charge DECIMAL(10, 2) NOT NULL,
            extra_toll_charge DECIMAL(10, 2) NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'extra_charges' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS extra_charges;";
        $this->db->exec($sql);
        echo "Table 'extra_charges' dropped.\n";
    }
}
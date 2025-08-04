<?php

namespace App\Database\Migrations;

use PDO;

class CreateTunnelChargesTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS tunnel_charges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            charge_start_date DATE NOT NULL,
            charge_end_date DATE NOT NULL,
            charge_amount DECIMAL(10, 2) NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'tunnel_charges' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS tunnel_charges;";
        $this->db->exec($sql);
        echo "Table 'tunnel_charges' dropped.\n";
    }
}
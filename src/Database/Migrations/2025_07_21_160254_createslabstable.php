<?php

namespace App\Database\Migrations;

use PDO;

class CreateSlabsTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS slabs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slab_value DECIMAL(10, 2) NOT NULL,
            slab_unit TINYINT(1) NOT NULL COMMENT '0=Mile, 1=Hour',
            slab_type TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Distance, 1=HourlyService',
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'slabs' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS slabs;";
        $this->db->exec($sql);
        echo "Table 'slabs' dropped.\n";
    }
}
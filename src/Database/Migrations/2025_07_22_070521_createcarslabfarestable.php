<?php

namespace App\Database\Migrations;

use PDO;

class CreateCarSlabFaresTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS car_slab_fares (
            id INT AUTO_INCREMENT PRIMARY KEY,
            car_id INT NOT NULL,
            slab_id INT NOT NULL,
            fare_amount DECIMAL(10, 2) NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
            FOREIGN KEY (slab_id) REFERENCES slabs(id) ON DELETE CASCADE
        );";

        $this->db->exec($sql);
        echo "Table 'car_slab_fares' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS car_slab_fares;";
        $this->db->exec($sql);
        echo "Table 'car_slab_fares' dropped.\n";
    }
}
<?php

namespace App\Database\Migrations;

use PDO;

class CreateOrdersTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id INT NOT NULL,
            square_payment_id VARCHAR(255) NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=pending, 1=paid, 2=failed',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(id)
        );";

        $this->db->exec($sql);
        echo "Table 'orders' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS orders;";
        $this->db->exec($sql);
        echo "Table 'orders' dropped.\n";
    }
}
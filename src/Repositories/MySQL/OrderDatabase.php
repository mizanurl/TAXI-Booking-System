<?php

namespace App\Repositories\MySQL;

use App\Models\Order;
use App\Repositories\Contracts\OrderInterface;
use PDO;
use PDOException;

class OrderDatabase implements OrderInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new order.
     * @param Order $order
     * @return int The ID of the newly created order.
     * @throws PDOException
     */
    public function create($order): int {
        
        $data = $order->toArray();

        $sql = "INSERT INTO orders (booking_id, square_payment_id, amount, status, created_at, updated_at)
                VALUES (:booking_id, :square_payment_id, :amount, :status, :created_at, :updated_at)";
        
        $stmt = $this->db->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':booking_id', $data['booking_id'], PDO::PARAM_INT);
        $stmt->bindParam(':square_payment_id', $data['square_payment_id']);
        $stmt->bindParam(':amount', $data['amount']);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $data['created_at']);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }
    
}
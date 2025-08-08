<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use PDOException;

interface OrderInterface
{
    /**
     * Create a new order.
     * @param Order $order
     * @return int The ID of the newly created order.
     * @throws PDOException
     */
    public function create(Order $order): int;
}
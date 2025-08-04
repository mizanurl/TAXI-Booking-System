<?php

namespace App\Repositories\MySQL;

use App\Models\TunnelCharge;
use App\Repositories\Contracts\TunnelChargeInterface;
use PDO;
use PDOException;

class TunnelChargeDatabase implements TunnelChargeInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a tunnel charge by its ID.
     * @param int $id
     * @return TunnelCharge|null
     * @throws PDOException
     */
    public function findById(int $id): ?TunnelCharge
    {
        $stmt = $this->db->prepare("SELECT * FROM tunnel_charges WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $tunnelChargeData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $tunnelChargeData ? TunnelCharge::fromArray($tunnelChargeData) : null;
    }

    /**
     * Get all tunnel charges.
     * @return TunnelCharge[]
     * @throws PDOException
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM tunnel_charges");
        $tunnelChargesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tunnelCharges = [];
        foreach ($tunnelChargesData as $data) {
            $tunnelCharges[] = TunnelCharge::fromArray($data);
        }
        return $tunnelCharges;
    }

    /**
     * Create a new tunnel charge.
     * @param TunnelCharge $tunnelCharge
     * @return int The ID of the newly created tunnel charge.
     * @throws PDOException
     */
    public function create(TunnelCharge $tunnelCharge): int
    {
        $data = $tunnelCharge->toDatabaseArray();
        unset($data['id']); // ID is auto-incremented

        $sql = "INSERT INTO tunnel_charges (charge_start_date, charge_end_date, charge_amount, status, created_at, updated_at)
                VALUES (:charge_start_date, :charge_end_date, :charge_amount, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':charge_start_date', $data['charge_start_date']);
        $stmt->bindParam(':charge_end_date', $data['charge_end_date']);
        $stmt->bindParam(':charge_amount', $data['charge_amount']);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $data['created_at']);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing tunnel charge.
     * @param TunnelCharge $tunnelCharge
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(TunnelCharge $tunnelCharge): bool
    {
        $data = $tunnelCharge->toDatabaseArray();
        if (!isset($data['id'])) {
            throw new PDOException("TunnelCharge ID is required for update.");
        }

        $sql = "UPDATE tunnel_charges SET
                    charge_start_date = :charge_start_date,
                    charge_end_date = :charge_end_date,
                    charge_amount = :charge_amount,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':charge_start_date', $data['charge_start_date']);
        $stmt->bindParam(':charge_end_date', $data['charge_end_date']);
        $stmt->bindParam(':charge_amount', $data['charge_amount']);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        return $stmt->execute();
    }

    /**
     * Delete a tunnel charge by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tunnel_charges WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
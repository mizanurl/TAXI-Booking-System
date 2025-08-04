<?php

namespace App\Repositories\MySQL;

use App\Models\Slab;
use App\Repositories\Contracts\SlabInterface;
use PDO;
use PDOException;

class SlabDatabase implements SlabInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a slab by its ID.
     * @param int $id
     * @return Slab|null
     * @throws PDOException
     */
    public function findById(int $id): ?Slab
    {
        $stmt = $this->db->prepare("SELECT * FROM slabs WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $slabData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $slabData ? Slab::fromArray($slabData) : null;
    }

    /**
     * Get all slabs.
     * @return Slab[]
     * @throws PDOException
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM slabs");
        $slabsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $slabs = [];
        foreach ($slabsData as $slabData) {
            $slabs[] = Slab::fromArray($slabData);
        }
        return $slabs;
    }

    /**
     * Create a new slab.
     * @param Slab $slab
     * @return int The ID of the newly created slab.
     * @throws PDOException
     */
    public function create(Slab $slab): int
    {
        $data = $slab->toDatabaseArray(); // Use toDatabaseArray for insertion

        $sql = "INSERT INTO slabs (slab_value, slab_unit, slab_type, status, created_at, updated_at)
                VALUES (:slab_value, :slab_unit, :slab_type, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':slab_value', $data['slab_value']);
        $stmt->bindParam(':slab_unit', $data['slab_unit'], PDO::PARAM_INT);
        $stmt->bindParam(':slab_type', $data['slab_type'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $data['created_at']);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing slab.
     * @param Slab $slab
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(Slab $slab): bool
    {
        $data = $slab->toDatabaseArray(); // Use toDatabaseArray for update

        $sql = "UPDATE slabs SET
                    slab_value = :slab_value,
                    slab_unit = :slab_unit,
                    slab_type = :slab_type,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':slab_value', $data['slab_value']);
        $stmt->bindParam(':slab_unit', $data['slab_unit'], PDO::PARAM_INT);
        $stmt->bindParam(':slab_type', $data['slab_type'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $data['updated_at']);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete a slab by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM slabs WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
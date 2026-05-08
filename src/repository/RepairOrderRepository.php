<?php

require_once __DIR__ . '/../models/RepairOrder.php';

use Enum\RepairOrderStatus;

/**
 * RepairOrderRepository — SQL operations for the `repair_orders` table.
 *
 * IMPORTANT: This repo respects the soft-delete pattern. Most reads
 * filter `deleted_at IS NULL` automatically.
 */
class RepairOrderRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?RepairOrder
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM repair_orders WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? RepairOrder::fromRow($row) : null;
    }

    public function findByRoNumber(string $roNumber): ?RepairOrder
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM repair_orders WHERE ro_number = ? AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([$roNumber]);
        $row = $stmt->fetch();
        return $row ? RepairOrder::fromRow($row) : null;
    }

    /**
     * Find by customer token — used by the customer portal.
     * Returns null unless the RO is currently in 'awaiting_approval'.
     * If the advisor reverts the workflow, the customer link stops
     * resolving until they re-advance (which generates a fresh token).
     */
    public function findByCustomerToken(string $token): ?RepairOrder
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM repair_orders
             WHERE customer_token = ?
               AND token_expires_at > ?
               AND status = 'awaiting_approval'
               AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$token, date('Y-m-d H:i:s')]);
        $row = $stmt->fetch();
        return $row ? RepairOrder::fromRow($row) : null;
    }

    public function findAllActiveForDashboard(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT ro.*, 
                    v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model,
                    c.full_name as customer_name,
                    u.full_name as technician_name
             FROM repair_orders ro
             LEFT JOIN vehicles v ON ro.vehicle_id = v.id
             LEFT JOIN customers c ON v.customer_id = c.id
             LEFT JOIN users u ON ro.technician_id = u.id
             WHERE ro.deleted_at IS NULL AND ro.status != 'closed'
             ORDER BY ro.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findActiveByTechnicianIdForDashboard(int $technicianId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT ro.*, 
                    v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model,
                    c.full_name as customer_name,
                    u.full_name as technician_name
             FROM repair_orders ro
             LEFT JOIN vehicles v ON ro.vehicle_id = v.id
             LEFT JOIN customers c ON v.customer_id = c.id
             LEFT JOIN users u ON ro.technician_id = u.id
             WHERE ro.technician_id = ?
               AND ro.status IN ('diagnosis', 'inspected')
               AND ro.deleted_at IS NULL
             ORDER BY ro.created_at ASC"
        );
        $stmt->execute([$technicianId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countsByStatus(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT status, COUNT(*) as count 
             FROM repair_orders 
             WHERE deleted_at IS NULL 
             GROUP BY status"
        );
        $stmt->execute();
        $counts = [];
        while ($row = $stmt->fetch()) {
            $counts[$row['status']] = (int)$row['count'];
        }
        return $counts;
    }

    public function findActiveByTechnicianId(int $technicianId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM repair_orders
             WHERE technician_id = ?
               AND status IN ('intake', 'diagnosis')
               AND deleted_at IS NULL
             ORDER BY created_at ASC"
        );
        $stmt->execute([$technicianId]);
        return $this->mapAll($stmt);
    }

    public function countWithRoNumberPrefix(string $prefix): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS cnt FROM repair_orders WHERE ro_number LIKE ?"
        );
        $stmt->execute([$prefix . '%']);
        $row = $stmt->fetch();
        return (int) $row['cnt'];
    }

    public function create(
        string $roNumber,
        int    $vehicleId,
        int    $advisorId,
        ?int   $technicianId,
        int    $mileage,
        string $complaint
    ): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO repair_orders
                (ro_number, vehicle_id, advisor_id, technician_id, mileage, complaint, status)
             VALUES (?, ?, ?, ?, ?, ?, 'intake') RETURNING id"
        );
        $stmt->execute([$roNumber, $vehicleId, $advisorId, $technicianId, $mileage, $complaint]);
        return (int) $stmt->fetchColumn();
    }

    public function assignTechnician(int $roId, int $technicianId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE repair_orders SET technician_id = ? WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$technicianId, $roId]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatus(int $roId, RepairOrderStatus $status): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE repair_orders SET status = ? WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$status->value, $roId]);
        return $stmt->rowCount() > 0;
    }

    public function markInspectionSubmitted(
        int    $roId,
        string $token,
        string $tokenExpiresAt
    ): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE repair_orders
             SET inspection_submitted_at = ?,
                 customer_token = ?,
                 token_expires_at = ?,
                 status = 'awaiting_approval'
             WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([date('Y-m-d H:i:s'), $token, $tokenExpiresAt, $roId]);
        return $stmt->rowCount() > 0;
    }

    public function close(int $roId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE repair_orders
             SET status = 'closed', closed_at = ?
             WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([date('Y-m-d H:i:s'), $roId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Used when reverting from Closed — wipes closed_at so the RO
     * doesn't look closed-but-active. Status change is done in a
     * separate call within the same service-level transaction.
     */
    public function clearClosedAt(int $roId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE repair_orders SET closed_at = NULL WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$roId]);
        return $stmt->rowCount() > 0;
    }

    public function softDelete(int $roId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE repair_orders SET deleted_at = ? WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([date('Y-m-d H:i:s'), $roId]);
        return $stmt->rowCount() > 0;
    }

    public function setCustomerTokenAndAdvance(
        int $roId, string $token, string $expiresAt, RepairOrderStatus $status
    ): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE repair_orders
             SET customer_token = ?, token_expires_at = ?, status = ?
             WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$token, $expiresAt, $status->value, $roId]);
        return $stmt->rowCount() > 0;
    }

    public function markInspectionDone(int $roId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE repair_orders
             SET inspection_submitted_at = ?, status = 'inspected'
             WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([date('Y-m-d H:i:s'), $roId]);
        return $stmt->rowCount() > 0;
    }

    private function mapAll(PDOStatement $stmt): array
    {
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = RepairOrder::fromRow($row);
        }
        return $results;
    }
}

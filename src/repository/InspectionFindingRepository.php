<?php

require_once __DIR__ . '/../models/InspectionFinding.php';

use Enum\ApprovalStatus;

/**
 * InspectionFindingRepository — SQL operations for inspection_findings.
 *
 * Findings ARE the recommended services. Each row is one item the technician
 * flagged that the customer must approve or decline.
 */
class InspectionFindingRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?InspectionFinding
    {
        $stmt = $this->pdo->prepare("SELECT * FROM inspection_findings WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? InspectionFinding::fromRow($row) : null;
    }

    /**
     * All findings for an RO, in creation order.
     * Used by both the customer portal and the advisor RO detail page.
     */
    public function findAllByRepairOrderId(int $roId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM inspection_findings
             WHERE repair_order_id = ?
             ORDER BY id ASC"
        );
        $stmt->execute([$roId]);
        return $this->mapAll($stmt);
    }

    public function findPendingByRepairOrderId(int $roId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM inspection_findings
             WHERE repair_order_id = ? AND approval_status = 'pending'
             ORDER BY id ASC"
        );
        $stmt->execute([$roId]);
        return $this->mapAll($stmt);
    }

    public function findApprovedByRepairOrderId(int $roId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM inspection_findings
             WHERE repair_order_id = ? AND approval_status = 'approved'
             ORDER BY id ASC"
        );
        $stmt->execute([$roId]);
        return $this->mapAll($stmt);
    }

    public function countByRepairOrderId(int $roId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS cnt FROM inspection_findings
             WHERE repair_order_id = ?"
        );
        $stmt->execute([$roId]);
        $row = $stmt->fetch();
        return (int) $row['cnt'];
    }

    /**
     * Count how many findings on an RO are still pending decision.
     * Used to detect "all decided → advance to repair status".
     */
    public function countPendingByRepairOrderId(int $roId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS cnt FROM inspection_findings
             WHERE repair_order_id = ? AND approval_status = 'pending'"
        );
        $stmt->execute([$roId]);
        $row = $stmt->fetch();
        return (int) $row['cnt'];
    }

    public function totalApprovedCost(int $roId): float
    {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(estimated_cost), 0) AS total
             FROM inspection_findings
             WHERE repair_order_id = ? AND approval_status = 'approved'"
        );
        $stmt->execute([$roId]);
        $row = $stmt->fetch();
        return (float) $row['total'];
    }

    public function create(
        int     $repairOrderId,
        string  $title,
        ?string $description,
        ?string $photoPath,
        float   $estimatedCost
    ): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO inspection_findings
                (repair_order_id, title, description, photo_path, estimated_cost)
             VALUES (?, ?, ?, ?, ?) RETURNING id"
        );
        $stmt->execute([
            $repairOrderId, $title, $description, $photoPath, $estimatedCost
        ]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Customer's approve/decline action.
     * Updates BOTH approval_status AND customer_decided_at atomically.
     * The repair_order_id check prevents cross-RO tampering: even if a
     * malicious customer guesses a finding ID, they need the right RO id too.
     */
    public function recordCustomerDecision(
        int            $findingId,
        int            $repairOrderId,
        ApprovalStatus $decision
    ): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE inspection_findings
             SET approval_status = ?, customer_decided_at = ?
             WHERE id = ? AND repair_order_id = ?"
        );
        $stmt->execute([$decision->value, date('Y-m-d H:i:s'), $findingId, $repairOrderId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Reset to pending — undo button on the customer portal.
     */
    public function resetToPending(int $findingId, int $repairOrderId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE inspection_findings
             SET approval_status = 'pending', customer_decided_at = NULL
             WHERE id = ? AND repair_order_id = ?"
        );
        $stmt->execute([$findingId, $repairOrderId]);
        return $stmt->rowCount() > 0;
    }

    public function updateDescriptionAndCost(int $findingId, ?string $description, float $estimatedCost): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE inspection_findings SET description = ?, estimated_cost = ? WHERE id = ?"
        );
        $stmt->execute([$description, $estimatedCost, $findingId]);
        return $stmt->rowCount() > 0;
    }

    public function setPhotoPath(int $findingId, ?string $photoPath): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE inspection_findings SET photo_path = ? WHERE id = ?"
        );
        $stmt->execute([$photoPath, $findingId]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $findingId, int $repairOrderId): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM inspection_findings WHERE id = ? AND repair_order_id = ?"
        );
        $stmt->execute([$findingId, $repairOrderId]);
        return $stmt->rowCount() > 0;
    }

    public function deleteAllForRepairOrder(int $roId): int
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM inspection_findings WHERE repair_order_id = ?"
        );
        $stmt->execute([$roId]);
        return $stmt->rowCount();
    }

    /**
     * Advisor edits a finding before it goes to the customer.
     * Updates title, description, and estimated cost together.
     */
    public function updateForAdvisor(
        int     $findingId,
        int     $repairOrderId,
        string  $title,
        ?string $description,
        float   $estimatedCost
    ): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE inspection_findings
             SET title = ?, description = ?, estimated_cost = ?
             WHERE id = ? AND repair_order_id = ?"
        );
        $stmt->execute([
            $title,
            $description,
            $estimatedCost,
            $findingId,
            $repairOrderId
        ]);
        return $stmt->rowCount() > 0;
    }

    private function mapAll(PDOStatement $stmt): array
    {
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = InspectionFinding::fromRow($row);
        }
        return $results;
    }
}

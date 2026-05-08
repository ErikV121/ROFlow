<?php

require_once __DIR__ . '/../models/RepairOrder.php';
require_once __DIR__ . '/../repository/RepairOrderRepository.php';
require_once __DIR__ . '/../repository/InspectionFindingRepository.php';

use Enum\RepairOrderStatus;

class InspectionService
{
    private PDO $pdo;
    private RepairOrderRepository $roRepo;
    private InspectionFindingRepository $findingRepo;

    public function __construct(
        PDO                         $pdo,
        RepairOrderRepository       $roRepo,
        InspectionFindingRepository $findingRepo
    )
    {
        $this->pdo = $pdo;
        $this->roRepo = $roRepo;
        $this->findingRepo = $findingRepo;
    }

    /**
     * Technician submits the inspection.
     * Atomic: every finding is saved, the RO is stamped, and the status
     * transitions to Inspected — or none of it happens.
     */
    public function submitInspection(int $roId, int $technicianId, array $findings): void
    {
        $ro = $this->roRepo->findById($roId);
        if ($ro === null) {
            throw new RuntimeException('Repair order not found.');
        }
        if ($ro->getTechnicianId() !== $technicianId) {
            throw new RuntimeException('You are not assigned to this RO.');
        }
        if ($ro->getStatus() !== RepairOrderStatus::Diagnosis) {
            throw new RuntimeException('Inspection can only be submitted while in Diagnosis.');
        }

        $cleaned = $this->cleanAndValidate($findings);

        $this->pdo->beginTransaction();
        try {
            foreach ($cleaned as $f) {
                $this->findingRepo->create(
                    $roId,
                    $f['title'],
                    $f['description'] ?: null,
                    null,
                    0.0
                );
            }

            $this->roRepo->markInspectionDone($roId);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function cleanAndValidate(array $findings): array
    {
        $cleaned = [];
        foreach ($findings as $i => $row) {
            $title = trim($row['title'] ?? '');
            $description = trim($row['description'] ?? '');

            if ($title === '' && $description === '') {
                continue;
            }
            if ($title === '') {
                throw new InvalidArgumentException("Row " . ($i + 1) . ": title is required.");
            }
            if (strlen($title) > 100) {
                throw new InvalidArgumentException("Row " . ($i + 1) . ": title is too long (max 100).");
            }

            $cleaned[] = ['title' => $title, 'description' => $description];
        }

        return $cleaned;
    }

    /**
     * Advisor saves edits to many findings in one shot.
     *
     * $findingsData is keyed by finding ID:
     *   [
     *     '12' => ['title' => '...', 'description' => '...', 'estimated_cost' => '49.99'],
     *     '13' => ['title' => '...', 'description' => '...', 'estimated_cost' => '0'],
     *   ]
     *
     * All rows are validated up front; any single invalid row aborts the
     * whole save and nothing gets written.
     */
    public function updateMany(int $roId, int $advisorId, array $findingsData): void
    {
        $ro = $this->roRepo->findById($roId);
        if ($ro === null) {
            throw new RuntimeException('Repair order not found.');
        }
        if ($ro->getAdvisorId() !== $advisorId) {
            throw new RuntimeException('You do not own this RO.');
        }
        if (in_array($ro->getStatus(), [
            RepairOrderStatus::AwaitingApproval,
            RepairOrderStatus::Repair,
            RepairOrderStatus::ReadyForPickup,
            RepairOrderStatus::Closed,
        ], true)) {
            throw new RuntimeException('Findings can no longer be edited at this stage.');
        }

        // Validate all rows BEFORE writing anything.
        $cleaned = [];
        foreach ($findingsData as $findingId => $data) {
            $findingId = (int) $findingId;
            if ($findingId <= 0) continue;

            $title       = trim((string) ($data['title'] ?? ''));
            $description = trim((string) ($data['description'] ?? ''));
            $cost        = (float) ($data['estimated_cost'] ?? 0);

            if ($title === '') {
                throw new InvalidArgumentException("Finding #$findingId: title is required.");
            }
            if (strlen($title) > 100) {
                throw new InvalidArgumentException("Finding #$findingId: title is too long (max 100).");
            }
            if ($cost < 0) {
                throw new InvalidArgumentException("Finding #$findingId: cost cannot be negative.");
            }

            $cleaned[$findingId] = [
                'title'          => $title,
                'description'    => $description !== '' ? $description : null,
                'estimated_cost' => $cost,
            ];
        }

        if (empty($cleaned)) {
            return; // nothing to save — no error
        }

        $this->pdo->beginTransaction();
        try {
            foreach ($cleaned as $findingId => $data) {
                $this->findingRepo->updateForAdvisor(
                    $findingId,
                    $roId,
                    $data['title'],
                    $data['description'],
                    $data['estimated_cost']
                );
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Single-finding update — kept for any callers that still need it.
     * New code should prefer updateMany() for batch edits.
     */
    public function updateFinding(
        int    $findingId,
        int    $roId,
        int    $advisorId,
        string $title,
        string $description,
        float  $estimatedCost
    ): void
    {
        $this->updateMany($roId, $advisorId, [
            $findingId => [
                'title'          => $title,
                'description'    => $description,
                'estimated_cost' => $estimatedCost,
            ],
        ]);
    }
}
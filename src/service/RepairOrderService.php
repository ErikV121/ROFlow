<?php

require_once __DIR__ . '/../models/RepairOrder.php';
require_once __DIR__ . '/../repository/RepairOrderRepository.php';
require_once __DIR__ . '/../repository/CustomerRepository.php';
require_once __DIR__ . '/../repository/VehicleRepository.php';
require_once __DIR__ . '/../repository/InspectionFindingRepository.php';
require_once __DIR__ . '/../helper/helper.php';

use Enum\RepairOrderStatus;

class RepairOrderService
{
    private PDO $pdo;
    private RepairOrderRepository $roRepo;
    private CustomerRepository $customerRepo;
    private VehicleRepository $vehicleRepo;
    private InspectionFindingRepository $findingRepo;

    public function __construct(
        PDO $pdo,
        RepairOrderRepository $roRepo,
        CustomerRepository $customerRepo,
        VehicleRepository $vehicleRepo,
        ?InspectionFindingRepository $findingRepo = null
    ) {
        $this->pdo = $pdo;
        $this->roRepo = $roRepo;
        $this->customerRepo = $customerRepo;
        $this->vehicleRepo = $vehicleRepo;
        $this->findingRepo = $findingRepo ?? new InspectionFindingRepository($pdo);
    }

    public function createIntake(array $data, int $advisorId): RepairOrder
    {
        $this->validateIntakeData($data);

        $this->pdo->beginTransaction();
        try {
            $customer = $this->customerRepo->findByPhone($data['phone']);
            $customerId = $customer
                ? $customer->getId()
                : $this->customerRepo->create(
                    $data['customer_name'],
                    $data['phone'],
                    $data['email'] ?: null
                );

            $vehicle = $this->vehicleRepo->findByVin($data['vin']);
            $vehicleId = $vehicle
                ? $vehicle->getId()
                : $this->vehicleRepo->create(
                    $customerId,
                    $data['vin'],
                    (int) $data['year'],
                    $data['make'],
                    $data['model'],
                    $data['color'] ?: null
                );

            $prefix = 'RO-' . date('Y') . '-';
            $sequence = $this->roRepo->countWithRoNumberPrefix($prefix) + 1;
            $roNumber = generate_ro_number($sequence);

            $roId = $this->roRepo->create(
                $roNumber,
                $vehicleId,
                $advisorId,
                null,
                (int) $data['mileage'],
                $data['complaint']
            );

            $this->pdo->commit();
            return $this->roRepo->findById($roId);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    public function advanceToNext(int $roId, int $userId): RepairOrder
    {
        $ro = $this->roRepo->findById($roId);
        if ($ro === null) {
            throw new RuntimeException('Repair order not found.');
        }

        $next = $ro->getStatus()->allowedNext()[0] ?? null;
        if ($next === null) {
            throw new RuntimeException('Already at the final state.');
        }

        if ($next === RepairOrderStatus::Inspected) {
            if ($this->findingRepo->countByRepairOrderId($roId) === 0) {
                throw new RuntimeException('Cannot mark inspection complete until at least one finding exists.');
            }
            $this->roRepo->markInspectionDone($roId);
        } elseif ($next === RepairOrderStatus::AwaitingApproval) {
            $token   = generate_customer_token();
            $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
            $this->roRepo->setCustomerTokenAndAdvance($roId, $token, $expires, $next);
        } elseif ($next === RepairOrderStatus::Closed) {
            $this->roRepo->close($roId);
        } else {
            $this->roRepo->updateStatus($roId, $next);
        }

        return $this->roRepo->findById($roId);
    }

    /**
     * Step the RO one state backwards. Click multiple times to go further.
     * Reverting from Closed also clears the closed_at timestamp.
     */
    public function revertStatus(int $roId, int $userId): RepairOrder
    {
        $ro = $this->roRepo->findById($roId);
        if ($ro === null) {
            throw new RuntimeException('Repair order not found.');
        }

        $current  = $ro->getStatus();
        $previous = $current->previous();
        if ($previous === null) {
            throw new RuntimeException('Cannot go back from ' . $current->label() . '.');
        }

        $this->pdo->beginTransaction();
        try {
            $this->roRepo->updateStatus($roId, $previous);
            if ($current === RepairOrderStatus::Closed) {
                $this->roRepo->clearClosedAt($roId);
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $this->roRepo->findById($roId);
    }


    private function validateIntakeData(array $data): void
    {
        $required = ['customer_name', 'phone', 'vin', 'year', 'make', 'model', 'mileage', 'complaint'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new InvalidArgumentException("Field '$field' is required.");
            }
        }

        $year = (int) $data['year'];
        if ($year < 1900 || $year > (int) date('Y') + 1) {
            throw new InvalidArgumentException('Invalid vehicle year.');
        }

        if ((int) $data['mileage'] < 0) {
            throw new InvalidArgumentException('Mileage cannot be negative.');
        }

        if (strlen($data['vin']) < 5) {
            throw new InvalidArgumentException('VIN looks too short.');
        }
    }

    public function startDiagnosis(int $roId, int $technicianId, int $advisorId): void
    {
        $ro = $this->roRepo->findById($roId);
        if ($ro === null) {
            throw new RuntimeException('Repair order not found.');
        }
        if ($ro->getStatus() !== RepairOrderStatus::Intake) {
            throw new RuntimeException('Can only start diagnosis from Intake.');
        }

        $this->pdo->beginTransaction();
        try {
            $this->roRepo->assignTechnician($roId, $technicianId);
            $this->roRepo->updateStatus($roId, RepairOrderStatus::Diagnosis);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Kept for backward compatibility with existing callers.
     * New code should use advanceToNext() — it handles this case generically.
     */
    public function sendForCustomerApproval(int $roId, int $advisorId): RepairOrder
    {
        $ro = $this->roRepo->findById($roId);
        if ($ro === null) {
            throw new RuntimeException('Repair order not found.');
        }
        if ($ro->getStatus() !== RepairOrderStatus::Inspected) {
            throw new RuntimeException('Can only send for approval after inspection is complete.');
        }

        return $this->advanceToNext($roId, $advisorId);
    }
}

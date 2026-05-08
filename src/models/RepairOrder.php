<?php


require_once __DIR__ . '/enum/RepairOrderStatus.php';

use Enum\RepairOrderStatus;


class RepairOrder
{
    private int $id;
    private string $roNumber;
    private int $vehicleId;
    private int $advisorId;
    private ?int $technicianId;
    private int $mileage;
    private string $complaint;
    private RepairOrderStatus $status;

    private ?string $customerToken;
    private ?string $tokenExpiresAt;
    private ?string $inspectionSubmittedAt;

    private ?string $createdAt;
    private ?string $closedAt;
    private ?string $deletedAt;

    public function __construct(
        int               $id,
        string            $roNumber,
        int               $vehicleId,
        int               $advisorId,
        ?int              $technicianId,
        int               $mileage,
        string            $complaint,
        RepairOrderStatus $status,
        ?string           $customerToken = null,
        ?string           $tokenExpiresAt = null,
        ?string           $inspectionSubmittedAt = null,
        ?string           $createdAt = null,
        ?string           $closedAt = null,
        ?string           $deletedAt = null
    )
    {
        $this->id = $id;
        $this->roNumber = $roNumber;
        $this->vehicleId = $vehicleId;
        $this->advisorId = $advisorId;
        $this->technicianId = $technicianId;
        $this->mileage = $mileage;
        $this->complaint = $complaint;
        $this->status = $status;
        $this->customerToken = $customerToken;
        $this->tokenExpiresAt = $tokenExpiresAt;
        $this->inspectionSubmittedAt = $inspectionSubmittedAt;
        $this->createdAt = $createdAt;
        $this->closedAt = $closedAt;
        $this->deletedAt = $deletedAt;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int)$row['id'],
            $row['ro_number'],
            (int)$row['vehicle_id'],
            (int)$row['advisor_id'],
            isset($row['technician_id']) ? (int)$row['technician_id'] : null,
            (int)$row['mileage'],
            $row['complaint'],
            RepairOrderStatus::from($row['status']),
            $row['customer_token'] ?? null,
            $row['token_expires_at'] ?? null,
            $row['inspection_submitted_at'] ?? null,
            $row['created_at'] ?? null,
            $row['closed_at'] ?? null,
            $row['deleted_at'] ?? null
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRoNumber(): string
    {
        return $this->roNumber;
    }

    public function getVehicleId(): int
    {
        return $this->vehicleId;
    }

    public function getAdvisorId(): int
    {
        return $this->advisorId;
    }

    public function getTechnicianId(): ?int
    {
        return $this->technicianId;
    }

    public function getMileage(): int
    {
        return $this->mileage;
    }

    public function getComplaint(): string
    {
        return $this->complaint;
    }

    public function getStatus(): RepairOrderStatus
    {
        return $this->status;
    }

    public function getCustomerToken(): ?string
    {
        return $this->customerToken;
    }

    public function getTokenExpiresAt(): ?string
    {
        return $this->tokenExpiresAt;
    }

    public function getInspectionSubmittedAt(): ?string
    {
        return $this->inspectionSubmittedAt;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getClosedAt(): ?string
    {
        return $this->closedAt;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }


    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function isClosed(): bool
    {
        return $this->status === RepairOrderStatus::Closed;
    }

    public function inspectionSubmitted(): bool
    {
        return $this->inspectionSubmittedAt !== null;
    }


    public function setTechnicianId(?int $technicianId): void
    {
        $this->technicianId = $technicianId;
    }

    public function setMileage(int $mileage): void
    {
        $this->mileage = $mileage;
    }

    public function setComplaint(string $complaint): void
    {
        $this->complaint = $complaint;
    }

    public function setStatus(RepairOrderStatus $status): void
    {
        $this->status = $status;
    }

    public function setCustomerToken(?string $token): void
    {
        $this->customerToken = $token;
    }

    public function setTokenExpiresAt(?string $expiresAt): void
    {
        $this->tokenExpiresAt = $expiresAt;
    }

    public function setInspectionSubmittedAt(?string $submittedAt): void
    {
        $this->inspectionSubmittedAt = $submittedAt;
    }

    public function setClosedAt(?string $closedAt): void
    {
        $this->closedAt = $closedAt;
    }

    public function setDeletedAt(?string $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }
}
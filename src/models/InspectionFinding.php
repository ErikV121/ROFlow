<?php

require_once __DIR__ . '/enum/ApprovalStatus.php';

use Enum\ApprovalStatus;

class InspectionFinding
{
    private int $id;
    private int $repairOrderId;
    private string $title;
    private ?string $description;
    private ?string $photoPath;
    private float $estimatedCost;
    private ApprovalStatus $approvalStatus;
    private ?string $customerDecidedAt;
    private ?string $createdAt;

    public function __construct(
        int            $id,
        int            $repairOrderId,
        string         $title,
        ?string        $description,
        ?string        $photoPath,
        float          $estimatedCost,
        ApprovalStatus $approvalStatus,
        ?string        $customerDecidedAt = null,
        ?string        $createdAt = null
    )
    {
        $this->id = $id;
        $this->repairOrderId = $repairOrderId;
        $this->title = $title;
        $this->description = $description;
        $this->photoPath = $photoPath;
        $this->estimatedCost = $estimatedCost;
        $this->approvalStatus = $approvalStatus;
        $this->customerDecidedAt = $customerDecidedAt;
        $this->createdAt = $createdAt;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int)$row['id'],
            (int)$row['repair_order_id'],
            $row['title'],
            $row['description'] ?? null,
            $row['photo_path'] ?? null,
            (float)$row['estimated_cost'],
            ApprovalStatus::from($row['approval_status']),
            $row['customer_decided_at'] ?? null,
            $row['created_at'] ?? null
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRepairOrderId(): int
    {
        return $this->repairOrderId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    public function getEstimatedCost(): float
    {
        return $this->estimatedCost;
    }

    public function getApprovalStatus(): ApprovalStatus
    {
        return $this->approvalStatus;
    }

    public function getCustomerDecidedAt(): ?string
    {
        return $this->customerDecidedAt;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function isPending(): bool
    {
        return $this->approvalStatus === ApprovalStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->approvalStatus === ApprovalStatus::Approved;
    }

    public function isDeclined(): bool
    {
        return $this->approvalStatus === ApprovalStatus::Declined;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setPhotoPath(?string $path): void
    {
        $this->photoPath = $path;
    }

    public function setEstimatedCost(float $cost): void
    {
        $this->estimatedCost = $cost;
    }

    public function setApprovalStatus(ApprovalStatus $status): void
    {
        $this->approvalStatus = $status;
    }

    public function setCustomerDecidedAt(?string $decidedAt): void
    {
        $this->customerDecidedAt = $decidedAt;
    }
}
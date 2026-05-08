<?php

namespace Enum;

enum RepairOrderStatus: string
{
    case Intake           = 'intake';
    case Diagnosis        = 'diagnosis';
    case Inspected        = 'inspected';
    case AwaitingApproval = 'awaiting_approval';
    case Repair           = 'repair';
    case ReadyForPickup   = 'ready_for_pickup';
    case Closed           = 'closed';

    public function label(): string {
        return match ($this) {
            self::Intake           => 'Intake',
            self::Diagnosis        => 'In Diagnosis',
            self::Inspected        => 'Inspection Complete',
            self::AwaitingApproval => 'Awaiting Customer Approval',
            self::Repair           => 'Under Repair',
            self::ReadyForPickup   => 'Ready for Pickup',
            self::Closed           => 'Closed',
        };
    }

    public function color(): string {
        return match ($this) {
            self::Intake           => '#2563eb',
            self::Diagnosis        => '#7c3aed',
            self::Inspected        => '#0891b2',
            self::AwaitingApproval => '#e63946',
            self::Repair           => '#6d28d9',
            self::ReadyForPickup   => '#047857',
            self::Closed           => '#6b7280',
        };
    }

    public function bg(): string {
        return match ($this) {
            self::Intake           => '#dbeafe',
            self::Diagnosis        => '#ede9fe',
            self::Inspected        => '#cffafe',
            self::AwaitingApproval => '#fee2e2',
            self::Repair           => '#f3e8ff',
            self::ReadyForPickup   => '#d1fae5',
            self::Closed           => '#f3f4f6',
        };
    }

    public function allowedNext(): array {
        return match($this) {
            self::Intake           => [self::Diagnosis],
            self::Diagnosis        => [self::Inspected],
            self::Inspected        => [self::AwaitingApproval],
            self::AwaitingApproval => [self::Repair],
            self::Repair           => [self::ReadyForPickup],
            self::ReadyForPickup   => [self::Closed],
            self::Closed           => [],
        };
    }

    public function canTransitionTo(self $next): bool {
        return in_array($next, $this->allowedNext(), true);
    }

    /** The immediate previous state. Null if at the start of the workflow. */
    public function previous(): ?self {
        return match($this) {
            self::Intake           => null,
            self::Diagnosis        => self::Intake,
            self::Inspected        => self::Diagnosis,
            self::AwaitingApproval => self::Inspected,
            self::Repair           => self::AwaitingApproval,
            self::ReadyForPickup   => self::Repair,
            self::Closed           => self::ReadyForPickup,
        };
    }
}
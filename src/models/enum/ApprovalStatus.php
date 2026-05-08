<?php

namespace Enum;

enum ApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'Awaiting Decision',
            self::Approved => 'Approved',
            self::Declined => 'Declined',
        };
    }
}
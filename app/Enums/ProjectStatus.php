<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case REVISION_REQUIRED = 'REVISION_REQUIRED';
    case REVISED = 'REVISED';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::REVISION_REQUIRED => 'Revision Required',
            self::REVISED => 'Revised',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED]);
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT              => [self::SUBMITTED],
            self::SUBMITTED          => [self::APPROVED, self::REVISION_REQUIRED, self::REJECTED],
            self::REVISION_REQUIRED  => [self::REVISED],
            self::REVISED            => [self::APPROVED, self::REVISION_REQUIRED, self::REJECTED],
            self::APPROVED           => [],
            self::REJECTED           => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions());
    }
}

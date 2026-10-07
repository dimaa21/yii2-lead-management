<?php

declare(strict_types=1);

namespace app\components;

enum LeadStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Converted = 'converted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In progress',
            self::Converted => 'Converted',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::New => 'status-new',
            self::InProgress => 'status-progress',
            self::Converted => 'status-converted',
            self::Rejected => 'status-rejected',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}

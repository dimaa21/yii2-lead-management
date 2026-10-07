<?php

declare(strict_types=1);

namespace app\components;

enum LeadSource: string
{
    case Facebook = 'facebook';
    case Google = 'google';
    case Telegram = 'telegram';
    case Website = 'website';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Facebook => 'Facebook',
            self::Google => 'Google',
            self::Telegram => 'Telegram',
            self::Website => 'Website',
            self::Other => 'Other',
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

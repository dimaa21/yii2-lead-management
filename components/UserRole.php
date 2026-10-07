<?php

declare(strict_types=1);

namespace app\components;

/**
 * Roles stored on user.role and mirrored into Yii RBAC.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Manager => 'Manager',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

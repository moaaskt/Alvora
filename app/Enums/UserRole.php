<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN_ALVORA = 'admin_alvora';
    case COMPANY_ADMIN = 'company_admin';
    case INSTRUCTOR = 'instructor';
    case STUDENT = 'student';

    /**
     * Get user-friendly Portuguese label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN_ALVORA => 'Administrador Alvora',
            self::COMPANY_ADMIN => 'Administrador da Empresa',
            self::INSTRUCTOR => 'Instrutor',
            self::STUDENT => 'Aluno',
        };
    }

    /**
     * Get all available role values.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

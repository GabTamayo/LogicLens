<?php

namespace App\Enums;

enum RoleName: string
{
    case TEACHER = 'teacher';
    case STUDENT = 'student';

    public function label(): string
    {
        return match ($this) {
            self::TEACHER => 'Teacher',
            self::STUDENT => 'Student',
        };
    }
}

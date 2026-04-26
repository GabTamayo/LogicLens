<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static JAVA_LANG()
 * @method static static PYTHON_LANG()
 */
final class ProgrammingLanguage extends Enum
{
    const JAVA = 'java';

    const PYTHON = 'python';

    public static function response(?string $value): ?string
    {
        $readableValues = [
            self::JAVA => 'Java',
            self::PYTHON => 'Python',
        ];

        return $readableValues[$value] ?? null;
    }

    public static function asSelectArray(): array
    {
        return [
            self::JAVA => 'Java',
            self::PYTHON => 'Python',
        ];
    }

    public static function request(?string $value): ?string
    {
        $readableValues = [
            'Java' => self::JAVA,
            'Python' => self::PYTHON,
        ];

        return $readableValues[$value] ?? null;
    }

    public static function fileExtensions(string $language): array
    {
        return match ($language) {
            self::JAVA => ['java'],
            self::PYTHON => ['py'],
            default => ['txt'],
        };
    }

    public static function fromFileExtension(string $extension): ?string
    {
        $extension = strtolower($extension);

        foreach (self::getValues() as $value) {
            if (in_array($extension, self::fileExtensions($value))) {
                return $value;
            }
        }

        return null;
    }
}

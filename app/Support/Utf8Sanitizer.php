<?php

namespace App\Support;

/**
 * Limpia strings para json_encode (evita "Malformed UTF-8 characters").
 */
class Utf8Sanitizer
{
    public static function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[self::sanitize($k)] = self::sanitize($v);
            }

            return $out;
        }

        if (is_string($value)) {
            return self::string($value);
        }

        return $value;
    }

    public static function string(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        if ($clean !== false) {
            return $clean;
        }

        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}

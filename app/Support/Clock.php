<?php

namespace App\Support;

/**
 * Format clock and calendar values that may already be DateTime instances.
 *
 * Eloquent datetime casts return objects. Passing those objects to strtotime()
 * throws a TypeError on PHP 8 and aborts the response.
 */
class Clock
{
    public static function timeLabel($value): ?string
    {
        return self::format($value, 'h:i A');
    }

    public static function dateLabel($value): ?string
    {
        return self::format($value, 'D, j F, Y');
    }

    private static function format($value, string $format): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format($format);
        }

        if (!is_string($value) || $value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : date($format, $timestamp);
    }
}

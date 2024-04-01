<?php

namespace App\Utils;

class Converter
{
    public function convert($value, $fromUnit, $toUnit)
    {
        // If neither have any units, or it units match
        if ((!$fromUnit && !$toUnit) ||  $fromUnit === $toUnit) {
            return $value;
        }

        // "cups" to "millilitres
        if ($fromUnit === 'cups' && $toUnit === 'millilitres') {
            return round($value * 236.588);
        }

        // "millilitres" to "cups
        if ($fromUnit === 'millilitres' && $toUnit === 'cups') {
            return round($value / 236.588);
        }

        // Failed to convert, return 0
        return 0;
    }
}

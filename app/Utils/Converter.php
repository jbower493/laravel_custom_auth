<?php

namespace App\Utils;

class Converter
{
    // Mass
    const GRAMS = 'grams';
    const POUNDS = 'pounds';
    const OUNCES = 'ounces';

    // Volume
    const CUPS = 'cups';
    const MILLILITRES = 'millilitres';
    const LITRES = 'litres';
    const FLUID_OUNCES = 'fluid ounces';
    const TEASPOONS = 'teaspoons';
    const TABLESPOONS = 'tablespoons';

    public function convert($value, $fromUnit, $toUnit)
    {
        // If neither have any units, or it units match
        if ((!$fromUnit && !$toUnit) ||  $fromUnit === $toUnit) {
            return $value;
        }

        /**
         * 
         * 
         * MASS
         * 
         * 
         */

        /**
         * GRAMS
         */
        // "grams" to "pounds
        if ($fromUnit === self::GRAMS && $toUnit === SELF::POUNDS) {
            return round($value * 0.0022);
        }

        // "grams" to "ounces
        if ($fromUnit === self::GRAMS && $toUnit === SELF::OUNCES) {
            return round($value * 0.0353);
        }

        /**
         * POUNDS
         */
        // "pounds" to "grams
        if ($fromUnit === self::POUNDS && $toUnit === SELF::GRAMS) {
            return round($value * 453.592);
        }

        // "pounds" to "ounces
        if ($fromUnit === self::POUNDS && $toUnit === SELF::OUNCES) {
            return round($value * 16);
        }

        /**
         * OUNCES
         */
        // "ounces" to "grams
        if ($fromUnit === self::OUNCES && $toUnit === SELF::GRAMS) {
            return round($value * 28.35);
        }

        // "ounces" to "pounds
        if ($fromUnit === self::OUNCES && $toUnit === SELF::POUNDS) {
            return round($value * 0.0625);
        }

        /**
         * 
         * 
         * VOLUME
         * 
         * 
         */

        /**
         * CUPS
         */
        // "cups" to "millilitres
        if ($fromUnit === self::CUPS && $toUnit === SELF::MILLILITRES) {
            return round($value * 236.588);
        }

        // "cups" to "litres"
        if ($fromUnit === self::CUPS && $toUnit === self::LITRES) {
            return round($value * 0.236);
        }

        // "cups" to "fluid ounces"
        if ($fromUnit === self::CUPS && $toUnit === self::FLUID_OUNCES) {
            return round($value * 8);
        }

        // "cups" to "teaspoons"
        if ($fromUnit === self::CUPS && $toUnit === self::TEASPOONS) {
            return round($value * 48);
        }

        // "cups" to "tablespoons"
        if ($fromUnit === self::CUPS && $toUnit === self::TABLESPOONS) {
            return round($value * 16);
        }

        /**
         * MILLILITRES
         */
        // "millilitres" to "cups
        if ($fromUnit === self::MILLILITRES && $toUnit === self::CUPS) {
            return round($value * 0.00422);
        }

        // "millilitres" to "litres"
        if ($fromUnit === self::MILLILITRES && $toUnit === self::LITRES) {
            return round($value * 0.001);
        }

        // "millilitres" to "fluid ounces"
        if ($fromUnit === self::MILLILITRES && $toUnit === self::FLUID_OUNCES) {
            return round($value * 0.0338);
        }

        // "millilitres" to "teaspoons"
        if ($fromUnit === self::MILLILITRES && $toUnit === self::TEASPOONS) {
            return round($value * 0.203);
        }

        // "millilitres" to "tablespoons"
        if ($fromUnit === self::MILLILITRES && $toUnit === self::CUPS) {
            return round($value * 0.0676);
        }

        /**
         * LITRES
         */
        // "litres" to "cups"
        if ($fromUnit === self::LITRES && $toUnit === self::CUPS) {
            return round($value * 4.227);
        }

        // "litres" to "millilitres"
        if ($fromUnit === self::LITRES && $toUnit === self::MILLILITRES) {
            return round($value * 1000);
        }

        // "litres" to "fluid ounces"
        if ($fromUnit === self::LITRES && $toUnit === self::FLUID_OUNCES) {
            return round($value * 33.814);
        }

        // "litres" to "teaspoons"
        if ($fromUnit === self::LITRES && $toUnit === self::TEASPOONS) {
            return round($value * 202.884);
        }

        // "litres" to "tablespoons"
        if ($fromUnit === self::LITRES && $toUnit === self::TABLESPOONS) {
            return round($value * 67.628);
        }

        /**
         * FLUID OUNCES
         */
        // "fluid ounces" to "cups"
        if ($fromUnit === self::FLUID_OUNCES && $toUnit === self::CUPS) {
            return round($value * 0.125);
        }

        // "fluid ounces" to "millilitres"
        if ($fromUnit === self::FLUID_OUNCES && $toUnit === self::MILLILITRES) {
            return round($value * 29.574);
        }

        // "fluid ounces" to "litres"
        if ($fromUnit === self::FLUID_OUNCES && $toUnit === self::LITRES) {
            return round($value * 0.0296);
        }

        // "fluid ounces" to "teaspoons"
        if ($fromUnit === self::FLUID_OUNCES && $toUnit === self::TEASPOONS) {
            return round($value * 6);
        }

        // "fluid ounces" to "tablespoons"
        if ($fromUnit === self::FLUID_OUNCES && $toUnit === self::TABLESPOONS) {
            return round($value * 2);
        }

        /**
         * TEASPOONS
         */
        // "teaspoons" to "cups"
        if ($fromUnit === self::TEASPOONS && $toUnit === self::CUPS) {
            return round($value * 0.0208);
        }

        // "teaspoons" to "millilitres"
        if ($fromUnit === self::TEASPOONS && $toUnit === self::MILLILITRES) {
            return round($value * 4.929);
        }

        // "teaspoons" to "litres"
        if ($fromUnit === self::TEASPOONS && $toUnit === self::LITRES) {
            return round($value * 0.00493);
        }

        // "teaspoons" to "fluid ounces"
        if ($fromUnit === self::TEASPOONS && $toUnit === self::FLUID_OUNCES) {
            return round($value * 0.167);
        }

        // "teaspoons" to "tablespoons"
        if ($fromUnit === self::TEASPOONS && $toUnit === self::TABLESPOONS) {
            return round($value * 0.334);
        }

        /**
         * TABLESPOONS
         */
        // "tablespoons" to "cups"
        if ($fromUnit === self::TABLESPOONS && $toUnit === self::CUPS) {
            return round($value * 0.0625);
        }

        // "tablespoons" to "millilitres"
        if ($fromUnit === self::TABLESPOONS && $toUnit === self::MILLILITRES) {
            return round($value * 14.787);
        }

        // "tablespoons" to "litres"
        if ($fromUnit === self::TABLESPOONS && $toUnit === self::LITRES) {
            return round($value * 0.0148);
        }

        // "tablespoons" to "fluid ounces"
        if ($fromUnit === self::TABLESPOONS && $toUnit === self::FLUID_OUNCES) {
            return round($value * 0.5);
        }

        // "tablespoons" to "teaspoons"
        if ($fromUnit === self::TABLESPOONS && $toUnit === self::TEASPOONS) {
            return round($value * 3);
        }

        // Failed to convert, return 0
        return 0;
    }
}

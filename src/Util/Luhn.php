<?php

namespace Kubinyete\Assertation\Util;

abstract class Luhn
{
    public static function check(string $string): bool
    {
        $digitsOnly = preg_replace('/[^0-9]+/', '', $string);
        $length = strlen($digitsOnly);
        $shouldDouble = false;
        $sum = 0;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = ord($digitsOnly[$i]) - ord('0');

            if ($shouldDouble) $digit *= 2;

            $sum += floor($digit / 10);
            $sum += $digit % 10;
            $shouldDouble = !$shouldDouble;
        }

        return ($sum % 10 === 0);
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSin implements ValidationRule
{
    /**
     * Run Luhn algorithm validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove any non-digit characters
        $sin = preg_replace('/\D/', '', $value);

        // SIN must be exactly 9 digits
        if (strlen($sin) !== 9) {
            $fail('The :attribute must be exactly 9 digits.');

            return;
        }

        // Apply the Luhn algorithm
        $checksum = 0;
        for ($i = 0; $i < 9; $i++) {
            $digit = (int) $sin[$i];

            if ($i % 2 === 1) { // Even indexed digits (1-based)
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $checksum += $digit;
        }

        if ($checksum % 10 !== 0) {
            $fail('The :attribute is not a valid SIN.');
        }
    }
}

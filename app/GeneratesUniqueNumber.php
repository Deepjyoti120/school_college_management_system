<?php

namespace App;

use Illuminate\Support\Str;

trait GeneratesUniqueNumber
{
    public function generateUniqueRandomNumber(string $prefix = 'REF', int $length = 10): string
    {
        // Random alphanumeric string
        $random = strtoupper(Str::random($length));

        // Combine with prefix and timestamp for uniqueness
        return $prefix . '-' . time() . '-' . $random;
    }
}

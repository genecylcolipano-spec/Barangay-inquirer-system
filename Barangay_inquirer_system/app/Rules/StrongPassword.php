<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = (string) $value;

        // Check minimum length
        if (strlen($password) < 8) {
            $fail('The password must be at least 8 characters long.');
            return;
        }

        // Check for uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $fail('The password must include at least one uppercase letter (A-Z).');
            return;
        }

        // Check for lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $fail('The password must include at least one lowercase letter (a-z).');
            return;
        }

        // Check for number
        if (!preg_match('/[0-9]/', $password)) {
            $fail('The password must include at least one number (0-9).');
            return;
        }

        // Check for special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', $password)) {
            $fail('The password must include at least one special character (!@#$%^&*).');
            return;
        }
    }
}

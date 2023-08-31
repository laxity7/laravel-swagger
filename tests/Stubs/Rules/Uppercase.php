<?php

namespace Mtrajano\LaravelSwagger\Tests\Stubs\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class Uppercase implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtoupper($value) !== $value) {
            $fail('The :attribute must be uppercase.');
        }
    }
}

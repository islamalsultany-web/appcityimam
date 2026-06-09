<?php

namespace App\Rules;

use App\Support\SecureUploadValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureAttachment implements ValidationRule
{
    public function __construct(private string $allowedMimesConfig) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail(SecureUploadValidator::failureMessage());

            return;
        }

        if (! SecureUploadValidator::isAllowed($value, $this->allowedMimesConfig)) {
            $fail(SecureUploadValidator::failureMessage());
        }
    }
}

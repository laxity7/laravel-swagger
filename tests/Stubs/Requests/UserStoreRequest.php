<?php

namespace Laxity7\LaravelSwagger\Tests\Stubs\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UserStoreRequest extends FormRequest
{
    public function validateResolved(): void
    {

    }

    public function rules(): array
    {
        return [
            'id' => [
                'integer',
                'required'
            ],
            'email' => 'required|email',
            'address' => 'string|required',
            'dob' => 'date|required',
            'picture' => 'file',
            'is_validated' => 'boolean',
            'score' => 'numeric',
            'account_type' => [
                'required',
                Rule::in(1, 2)
            ],
            'language_spoken' => 'required|in:en,es'
        ];
    }
}

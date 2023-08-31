<?php

namespace Mtrajano\LaravelSwagger\Tests\Stubs\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UserShowRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'show_relationships' => 'boolean'
        ];
    }
}

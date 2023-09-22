<?php

namespace Mtrajano\LaravelSwagger\Tests\Stubs\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UserShowRequest
 * @property bool $show_relationships Is it necessary to show the relationship
 */
final class UserShowRequest extends FormRequest
{
    /**
     * @var bool Is it necessary to show the status
     */
    public bool $show_status;

    public function rules(): array
    {
        return [
            'show_relationships' => 'boolean',
            'show_status' => 'required|boolean',
            'show_name' => 'boolean',
        ];
    }
}

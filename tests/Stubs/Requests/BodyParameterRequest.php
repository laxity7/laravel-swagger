<?php

namespace Mtrajano\LaravelSwagger\Tests\Stubs\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mtrajano\LaravelSwagger\Tests\Stubs\Rules\Uppercase as UppercaseRule;

/**
 * Get all body parameters.
 *
 * Use this route to get all body parameters.
 *
 * @property string $address User's home address
 * @property string $dob User's date of birth
 */
final class BodyParameterRequest extends FormRequest
{
    /** @var string User id */
    public string $id;
    /** @var string User email */
    public $email;

    public function rules(): array
    {
        return [
            'id' => 'integer|required',
            'email' => 'email|required',
            'address' => 'string|required',
            'dob' => 'date|required',
            'picture' => 'file',
            'is_validated' => 'boolean',
            'score' => 'numeric',

            'account_type' => 'integer|in:1,2|in_array:foo',

            'matrix' => 'array',
            'matrix.*' => 'array',
            'matrix.*.*' => 'integer',

            'points' => 'array',
            'points.*.x' => 'numeric',
            'points.*.y' => 'numeric',

            'point' => '',
            'point.x' => 'numeric',
            'point.y' => 'numeric',

            'type' => [
                Rule::in(1, 2, 3),
                'integer',
            ],

            'name' => [
                'string',
                new UppercaseRule,
            ],
            'name_too' => [
                'string',
                static function ($attribute, $value, $fail) {
                    if ($value === 'foo') {
                        $fail($attribute.' is invalid.');
                    }
                },
            ],
        ];
    }
}

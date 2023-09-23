<?php

namespace Laxity7\LaravelSwagger\Tests\Stubs\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Laxity7\LaravelSwagger\Tests\Stubs\Rules\Uppercase as UppercaseRule;

/**
 * Get all body parameters.
 *
 * Use this route to get all body parameters.
 *
 * @property string $address User's home address
 * @property string $dob User's date of birth
 * @property $picture Ignored this description
 */
final class BodyParameterRequest extends FormRequest
{
    /** @var string User id */
    public string $id;
    /** @var string User email */
    public $email;
    /**
     * Is it validated data?
     *
     * @var bool Ignored this description
     */
    public $is_validated;
    public $score;
    /**
     * This is a picture
     */
    public $picture;

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

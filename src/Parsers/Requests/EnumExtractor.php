<?php

namespace Laxity7\LaravelSwagger\Parsers\Requests;

use BackedEnum;
use UnitEnum;

final class EnumExtractor
{
    public function __construct(
        /**
         * @var UnitEnum|class-string<UnitEnum>
         */
        public readonly UnitEnum|string $enum,
    ) {
    }

    /**
     * Get enum values
     *
     * @return list<string|int>
     */
    public function getValues(): array
    {
        return match (true) {
            is_subclass_of($this->enum, BackedEnum::class) => array_column($this->enum::cases(), 'value'),
            default => array_column($this->enum::cases(), 'name'),
        };
    }

    /**
     * Get enum value type
     *
     * @return string
     */
    public function getType(): string
    {
        return gettype($this->getValues()[0] ?? '');
    }

    /**
     * Get enum value
     *
     * @param  UnitEnum  $value
     * @return string|int
     */
    public static function getValue(UnitEnum $value): string|int
    {
        return match (true) {
            is_subclass_of($value, BackedEnum::class) => $value->value,
            default => $value->name,
        };
    }
}

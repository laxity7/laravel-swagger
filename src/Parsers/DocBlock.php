<?php

namespace Laxity7\LaravelSwagger\Parsers;

use phpDocumentor\Reflection\DocBlock as PhpDocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * @mixin PhpDocBlock
 */
final class DocBlock
{
    public function __construct(
        public readonly PhpDocBlock $docBlock,
    ) {

    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->docBlock->$name(...$arguments);
    }

    public function getTagDescription(string $tagName, string $default = ''): string
    {
        $tag = $this->getTagByName($tagName);
        if ($tag === null) {
            return $default;
        }

        return $tag->getDescription()?->render() ?? $default;
    }

    public function getTagDescriptionForVariable(string $tagName, string $variableName, string $default = ''): string
    {
        $tag = $this->getTagByName($tagName);
        if ($tag === null) {
            return $default;
        }

        /** @var Tag $tag */
        foreach ($this->docBlock->getTagsByName($tagName) as $tag) {
            if ($tag->getVariableName() === $variableName) {
                return $tag->getDescription()?->render() ?? $default;
            }
        }

        return $default;
    }


    public function getTagByName(string $tagName): ?Tag
    {
        return $this->docBlock->getTagsByName($tagName)[0] ?? null;
    }
}

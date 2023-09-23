<?php

namespace Laxity7\LaravelSwagger\Parsers;

use Illuminate\Routing\RouteAction;
use Illuminate\Support\Reflector as IlluminateReflector;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Laravel\SerializableClosure\UnsignedSerializableClosure;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use Reflector;
use WeakMap;

final class ReflectionHelper
{
    private static WeakMap $cache;
    private static array $cacheArr = [];
    private static DocBlockFactory $docParser;
    private static array $classes = [];

    private function __construct()
    {
    }

    private static function cache(string|callable|object $key, callable $value): ReflectionFunction|ReflectionMethod|ReflectionClass|DocBlock
    {
        self::$cache ??= new WeakMap();

        if (is_string($key)) {
            if (!isset(self::$cacheArr[$key])) {
                self::$cacheArr = [];
                self::$cacheArr[$key] = $value();
            }

            return self::$cacheArr[$key];
        }

        self::$cache[$key] ??= $value();

        return self::$cache[$key];
    }

    public static function getClass(string|object $class, object $key): ReflectionClass
    {
        return self::cache($key, static fn() => new ReflectionClass($class));
    }

    public static function getRouteClass(array $action): ?ReflectionClass
    {
        $key = $action['uses'];
        if (!is_string($key)) {
            return null;
        }

        [$class,] = Str::parseCallback($key);

        return self::cache($class, static fn() => new ReflectionClass($class));
    }

    public static function getRouteAction(array $action): ReflectionFunction|ReflectionMethod|null
    {
        return self::cache($action['uses'], static fn() => self::getReflectionMethod($action));
    }

    private static function getDocBlockParser(): DocBlockFactory
    {
        if (!isset(self::$docParser)) {
            self::$docParser = DocBlockFactory::createInstance();
        }
        return self::$docParser;
    }

    public static function parseDocBlock(Reflector $reflection): DocBlock
    {
        return self::cache($reflection, static fn() => self::parseDocBlockFromString($reflection->getDocComment() ?: '/** */'));
    }

    public static function parseDocBlockFromString(string $docComment): DocBlock
    {
        return new DocBlock(self::getDocBlockParser()->create($docComment ?: '/** */'));
    }

    private static function getReflectionMethod(array $action): ReflectionFunction|ReflectionMethod|null
    {
        $callback = RouteAction::containsSerializedClosure($action)
            ? unserialize($action['uses'], ['allowed_classes' => [SerializableClosure::class, UnsignedSerializableClosure::class]])->getClosure()
            : $action['uses'];

        if (is_callable($callback)) {
            return new ReflectionFunction($callback);
        }

        [$class, $method] = Str::parseCallback($callback);
        if (!method_exists($class, $method) && IlluminateReflector::isCallable($class, $method)) {
            return null;
        }

        return new ReflectionMethod($class, $method);
    }

    public static function normalizeClassType(ReflectionClass $reflectionClass, string $type): ?string
    {
        if (empty($type) || self::isBaseType($type)) {
            return null;
        }

        $key = $reflectionClass->getNamespaceName().'_'.$type;
        if (array_key_exists($key, self::$classes)) {
            return self::$classes[$key];
        }

        $class = $type;
        if (!str_contains($class, '\\')) {
            $class = $reflectionClass->getNamespaceName().'\\'.$type;
            if (!class_exists($class)) {
                $classText = file_get_contents($reflectionClass->getFileName());
                preg_match(sprintf('/use (([\w_\\\\])+%s)/', $type), $classText, $matches);

                $class = $matches[1] ?? null;
            }
        }

        $isClass = $class && class_exists($class);
        self::$classes[$key] = $isClass ? $class : null;

        return self::$classes[$key];
    }

    /**
     * Determines if a type is a base type
     *
     * @param  string  $type
     * @return bool
     */
    private static function isBaseType(string $type): bool
    {
        return in_array($type, ['string', 'int', 'float', 'bool', 'array'], true);
    }

    public static function getPropertyDescription(ReflectionClass $reflectionClass, string $propertyName, string $default = ''): ?string
    {
        if (!$reflectionClass->hasProperty($propertyName)) {
            return $default;
        }

        $property = $reflectionClass->getProperty($propertyName);
        $docBlock = self::parseDocBlock($property);
        if ($docBlock->getSummary()) {
            return $docBlock->getSummary();
        }

        return $docBlock->getTagDescription('var', $default);
    }
}

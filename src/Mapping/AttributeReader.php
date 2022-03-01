<?php

namespace Dreadnip\SmartDtoBundle\Mapping;

use Dreadnip\SmartDtoBundle\Exception\DataTransferObjectException;
use ReflectionClass;

class AttributeReader
{
    public static function getAttribute(string $class, string $attributeName): ?object
    {
        if (!class_exists($class)) {
            throw DataTransferObjectException::nonExistentClass($class);
        }

        $reflectionClass = new ReflectionClass($class);

        $attributes = $reflectionClass->getAttributes($attributeName);

        if ($attributes) {
            // Both the attributes we're reading are non-repeatable, so we only support one instance
            $attribute = reset($attributes);

            return $attribute->newInstance();
        }

        return null;
    }
}

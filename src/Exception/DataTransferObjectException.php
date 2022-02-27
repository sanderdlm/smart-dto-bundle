<?php

namespace Dreadnip\SmartDtoBundle\Exception;

use Exception;

class DataTransferObjectException extends Exception
{
    public static function missingProperty(
        string $propertyName,
        string $className,
    ): self {
        return new self(sprintf(
            'Missing property "%s" on class "%s".',
            $propertyName,
            $className
        ));
    }

    public static function missingPropertyTypeHint(
        string $propertyName,
        string $className,
    ): self {
        return new self(sprintf(
            'Missing type hint for property "%s" on class "%s".',
            $propertyName,
            $className
        ));
    }

    public static function missingMethod(
        string $methodName,
        string $className,
    ): self {
        return new self(sprintf(
            'Missing method "%s" on class "%s".',
            $methodName,
            $className
        ));
    }

    public static function missingAttribute(
        string $attributeName,
        string $className,
        ?string $parentClassName = null,
    ): self {
        return new self(sprintf(
            'Missing attribute "%s" on class "%s" and parent class "%s".',
            $attributeName,
            $className,
            $parentClassName
        ));
    }

    public static function nonExistentClass(
        string $className,
    ): self {
        return new self(sprintf(
            'Class "%s" does not exist.',
            $className
        ));
    }

    public static function mappedClassMismatch(
        string $mappedClassName,
        string $passedClassname,
    ): self {
        return new self(sprintf(
            'Passed object "%s" is not the same class as configured by the MapsTo attribute (%s)',
            $passedClassname,
            $mappedClassName
        ));
    }


}

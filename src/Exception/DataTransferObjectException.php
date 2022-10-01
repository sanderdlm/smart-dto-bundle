<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Exception;

use Exception;

class DataTransferObjectException extends Exception
{
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

    public static function nonExistentClass(
        string $className,
    ): self {
        return new self(sprintf(
            'Class "%s" does not exist.',
            $className
        ));
    }

    public static function missingTrait(
        string $className,
    ): self {
        return new self(sprintf(
            'Class "%s" does not use the DataMapper trait.',
            $className
        ));
    }
}

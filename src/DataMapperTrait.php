<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle;

use Dreadnip\SmartDtoBundle\Hydration\DataTransferObjectHydrator;
use ReflectionClass;

trait DataMapperTrait
{
    protected ?object $source = null;

    final public static function from(object $source): object
    {
        $dtoClass = get_class();
        $newInstance = (new ReflectionClass($dtoClass))->newInstanceWithoutConstructor();

        $newInstance->source = $source;

        return (new DataTransferObjectHydrator())->hydrate($newInstance, $source);
    }

    public function getSource(): ?object
    {
        return $this->source;
    }

    public function hasSource(): bool
    {
        return $this->source !== null;
    }
}

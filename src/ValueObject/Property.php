<?php

namespace Dreadnip\SmartDtoBundle\ValueObject;

use Doctrine\ORM\Mapping\Entity;
use Dreadnip\SmartDtoBundle\DataTransferObject\AbstractDataTransferObject;
use Dreadnip\SmartDtoBundle\Mapping\AttributeReader;
use ReflectionNamedType;

class Property
{
    private string $name;
    private ReflectionNamedType $type;
    private mixed $match;

    public function __construct(
        string $name,
        ReflectionNamedType $type,
        mixed $match
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->match = $match;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type->getName();
    }

    public function getMatch(): mixed
    {
        return $this->match;
    }

    public function isBuiltin(): bool
    {
        return $this->type->isBuiltin();
    }

    public function isEntity(): bool
    {
        if (!$this->isBuiltin()) {
            return false;
        }

        return AttributeReader::getAttribute($this->name, Entity::class) !== null;
    }

    public function isDataTransferObject(): bool
    {
        return is_subclass_of($this->match, AbstractDataTransferObject::class);
    }
}

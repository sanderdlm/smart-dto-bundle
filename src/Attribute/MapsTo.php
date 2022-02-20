<?php

namespace Dreadnip\SmartDtoBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class MapsTo
{
    private string $entity;

    public function __construct(
        string $entity
    ) {
        $this->entity = $entity;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }
}

<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class MapsTo
{
    public function __construct(
        public readonly string $dataTransferObject
    ) {
    }
}

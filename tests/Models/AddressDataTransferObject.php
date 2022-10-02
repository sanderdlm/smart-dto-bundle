<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Test\Models;

use Dreadnip\SmartDtoBundle\DataMapperTrait;

class AddressDataTransferObject
{
    use DataMapperTrait;

    public ?string $street = null;

    public ?string $number = null;

    public ?string $zipCode = null;

    public ?string $city = null;

    public ?Province $province = null;
}

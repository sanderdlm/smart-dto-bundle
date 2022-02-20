<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Test\Models;

use Dreadnip\SmartDtoBundle\Attribute\MapsTo;
use Dreadnip\SmartDtoBundle\DataTransferObject\AbstractDataTransferObject;

#[MapsTo(entity:Address::class)]
class AddressDataTransferObject extends AbstractDataTransferObject
{
    public ?string $street = null;

    public ?string $number = null;

    public ?string $zipCode = null;

    public ?string $city = null;

    public ?Province $province = null;
}

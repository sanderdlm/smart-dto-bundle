<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Test\Models;

use Doctrine\Common\Collections\Collection;
use Dreadnip\SmartDtoBundle\DataMapperTrait;

class PersonDataTransferObject
{
    use DataMapperTrait;

    public ?string $firstName = null;

    public ?string $lastName = null;

    public ?AddressDataTransferObject $address = null;

    public ?PersonDataTransferObject $bestFriend = null;

    public ?Collection $friends = null;
}

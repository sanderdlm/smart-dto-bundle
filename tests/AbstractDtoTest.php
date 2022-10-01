<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Test;

use Dreadnip\SmartDtoBundle\Test\Models\Address;
use Dreadnip\SmartDtoBundle\Test\Models\AddressDataTransferObject;
use Dreadnip\SmartDtoBundle\Test\Models\Person;
use Dreadnip\SmartDtoBundle\Test\Models\PersonDataTransferObject;
use Dreadnip\SmartDtoBundle\Test\Models\Province;
use PHPUnit\Framework\TestCase;

class DataMapperTest extends TestCase
{
    private Person $dummyEntity;

    protected function setUp(): void
    {
        $luke = new Person(
            'Luke',
            'Skywalker',
            new Address(
                'Main street',
                '5B',
                '123456',
                'Seattle',
                Province::Antwerp
            ),
            null
        );

        $leia = new Person(
            'Leia',
            'Skywalker',
            new Address(
                'Main street',
                '5B',
                '123456',
                'Seattle',
                Province::Antwerp
            ),
            null
        );

        $this->dummyEntity = new Person(
            'John',
            'Doe',
            new Address(
                'Main street',
                '5B',
                '123456',
                'Seattle',
                Province::Antwerp
            ),
            $luke
        );

        $this->dummyEntity->addFriend($luke);
        $this->dummyEntity->addFriend($leia);
    }

    public function testNamedConstructor(): void
    {
        $dto = PersonDataTransferObject::from($this->dummyEntity);
        dump($dto);
        $this->assertInstanceOf(AddressDataTransferObject::class, $dto->address);
        $this->assertInstanceOf(PersonDataTransferObject::class, $dto->bestFriend);
        $this->assertNull($dto->bestFriend->bestFriend);
    }
}

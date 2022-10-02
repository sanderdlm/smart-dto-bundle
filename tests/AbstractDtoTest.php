<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Test;

use DateTime;
use Dreadnip\SmartDtoBundle\Test\Models\Address;
use Dreadnip\SmartDtoBundle\Test\Models\AddressDataTransferObject;
use Dreadnip\SmartDtoBundle\Test\Models\Person;
use Dreadnip\SmartDtoBundle\Test\Models\PersonDataTransferObject;
use Dreadnip\SmartDtoBundle\Test\Models\Province;
use Dreadnip\SmartDtoBundle\Test\Models\UpdatePerson;
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
            $luke,
            new DateTime('now')
        );

        $this->dummyEntity->addFriend($luke);
        $this->dummyEntity->addFriend($leia);
    }

    public function testFirstLevelNestedConversion(): void
    {
        $dto = UpdatePerson::from($this->dummyEntity);

        $this->assertInstanceOf(AddressDataTransferObject::class, $dto->address);
        $this->assertInstanceOf(PersonDataTransferObject::class, $dto->bestFriend);
    }

    public function testEnumHydration(): void
    {
        $dto = UpdatePerson::from($this->dummyEntity);

        $this->assertInstanceOf(Province::class, $dto->bestFriend->address->province);
    }

    public function testDateTimeHydration(): void
    {
        $dto = UpdatePerson::from($this->dummyEntity);

        $this->assertInstanceOf(DateTime::class, $dto->lastCheckIn);
    }

    public function testCollections(): void
    {
        $dto = UpdatePerson::from($this->dummyEntity);

        foreach ($dto->friends as $friend) {
            $this->assertInstanceOf(PersonDataTransferObject::class, $friend);
        }
    }
}

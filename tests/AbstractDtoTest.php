<?php

namespace Dreadnip\SmartDtoBundle\Test;

use Dreadnip\SmartDtoBundle\Factory\EntityFactory;
use Dreadnip\SmartDtoBundle\Test\Models\Address;
use Dreadnip\SmartDtoBundle\Test\Models\AddressDataTransferObject;
use Dreadnip\SmartDtoBundle\Test\Models\CreatePerson;
use Dreadnip\SmartDtoBundle\Test\Models\Person;
use Dreadnip\SmartDtoBundle\Test\Models\PersonDataTransferObject;
use Dreadnip\SmartDtoBundle\Test\Models\Province;
use Dreadnip\SmartDtoBundle\Test\Models\UpdatePerson;
use Dreadnip\SmartDtoBundle\Test\Models\UpdatePersonWithConstructor;
use PHPUnit\Framework\TestCase;

class AbstractDtoTest extends TestCase
{
    private Person $dummyEntity;

    protected function setUp(): void
    {
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
            new Person(
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
            )
        );
    }

    public function testNamedConstructor(): void
    {
        $dto = PersonDataTransferObject::fromEntity($this->dummyEntity);

        $this->assertInstanceOf(AddressDataTransferObject::class, $dto->address);
        $this->assertInstanceOf(PersonDataTransferObject::class, $dto->bestFriend);
        $this->assertNull($dto->bestFriend->bestFriend);
    }

    public function testEntityCreation(): void
    {
        $factory = new EntityFactory();

        $dto = CreatePerson::fromEntity($this->dummyEntity);
        $dummy = $factory->create(Person::class, $dto);

        $newEntity = $dto->create();

        $newEntity->


        $this->assertInstanceOf(Person::class, $newEntity);
        $this->assertEquals('John', $newEntity->getFirstName());
        $this->assertEquals('Doe', $newEntity->getLastName());
        $this->assertInstanceOf(Address::class, $newEntity->getAddress());
        $this->assertEquals('Main street', $newEntity->getAddress()->getStreet());
        $this->assertEquals('5B', $newEntity->getAddress()->getNumber());
        $this->assertEquals('Seattle', $newEntity->getAddress()->getCity());
        $this->assertEquals('123456', $newEntity->getAddress()->getZipCode());
        $this->assertEquals(Province::Antwerp, $newEntity->getAddress()->getProvince());
        $this->assertInstanceOf(Person::class, $newEntity->getBestFriend());
        $this->assertInstanceOf(Address::class, $newEntity->getBestFriend()->getAddress());
    }

    public function testEntityCreationAfterHydration(): void
    {
        $dto = UpdatePersonWithConstructor::fromEntity($this->dummyEntity);

        /** @var Person $this->>$this->dummyEntity */
        $newEntity = $dto->create();

        $this->assertInstanceOf(Person::class, $newEntity);
        $this->assertEquals('Jesus', $newEntity->getFirstName());
        $this->assertEquals('Doe', $newEntity->getLastName());
        $this->assertInstanceOf(Address::class, $newEntity->getAddress());
        $this->assertEquals('Main street', $newEntity->getAddress()->getStreet());
        $this->assertEquals('5B', $newEntity->getAddress()->getNumber());
        $this->assertEquals('Seattle', $newEntity->getAddress()->getCity());
        $this->assertEquals('123456', $newEntity->getAddress()->getZipCode());
        $this->assertEquals(Province::Antwerp, $newEntity->getAddress()->getProvince());
        $this->assertInstanceOf(Person::class, $newEntity->getBestFriend());
        $this->assertInstanceOf(Address::class, $newEntity->getBestFriend()->getAddress());
    }

    public function testUpdate(): void
    {
        $dto = UpdatePerson::fromEntity($this->dummyEntity);

        $this->assertInstanceOf(PersonDataTransferObject::class, $dto);
        $this->assertInstanceOf(AddressDataTransferObject::class, $dto->address);
        $this->assertInstanceOf(PersonDataTransferObject::class, $dto->bestFriend);

        $dto->firstName = 'Freddy';
        $dto->address->street = 'Foo street';
        $dto->bestFriend->lastName = 'Bar';

        /** @var Person $updatedEntity */
        $updatedEntity = $dto->update();

        $this->assertInstanceOf(Person::class, $updatedEntity);
        $this->assertInstanceOf(Address::class, $updatedEntity->getAddress());
        $this->assertInstanceOf(Person::class, $updatedEntity->getBestFriend());
        $this->assertEquals('Freddy', $updatedEntity->getFirstName());
        $this->assertEquals('Foo street', $updatedEntity->getAddress()->getStreet());
        $this->assertEquals('Bar', $updatedEntity->getBestFriend()->getLastName());
    }

    public function testUpdateWithSetValue(): void
    {
        $dto = UpdatePersonWithConstructor::fromEntity($this->dummyEntity);

        /** @var Person $updatedEntity */
        $updatedEntity = $dto->update();

        $this->assertNotEquals('Luke', $updatedEntity->getFirstName());
        $this->assertEquals('Jesus', $updatedEntity->getFirstName());
    }

    public function testDelete(): void
    {
        $dto = UpdatePerson::fromEntity($this->dummyEntity);

        $dto->bestFriend = null;

        /** @var Person $updatedEntity */
        $updatedEntity = $dto->update();

        $this->assertNull($updatedEntity->getBestFriend());
    }
}

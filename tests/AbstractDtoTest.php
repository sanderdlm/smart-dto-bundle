<?php

namespace Dreadnip\SmartDtoBundle\Test;

use Doctrine\Common\Collections\ArrayCollection;
use Dreadnip\SmartDtoBundle\Test\Models\Address;
use Dreadnip\SmartDtoBundle\Test\Models\AddressDataTransferObject;
use Dreadnip\SmartDtoBundle\Test\Models\Person;
use Dreadnip\SmartDtoBundle\Test\Models\PersonDataTransferObject;
use Dreadnip\SmartDtoBundle\Test\Models\Province;
use PHPUnit\Framework\TestCase;

class AbstractDtoTest extends TestCase
{
    public function testEmptyConstruct(): void
    {
        $dto = new PersonDataTransferObject();

        $this->assertInstanceOf(PersonDataTransferObject::class, $dto);
        $this->assertNull($dto->firstName);
        $this->assertNull($dto->lastName);
        $this->assertNull($dto->address);
        $this->assertNull($dto->bestFriend);
        $this->assertNull($dto->friends);

        $dto->firstName = 'John';
        $dto->lastName = 'Doe';
        $dto->address = new AddressDataTransferObject();
        $dto->bestFriend = new PersonDataTransferObject();
        $dto->friends = new ArrayCollection();
    }

    public function testCreate(): void
    {
        $addressDto = new AddressDataTransferObject();
        $addressDto->street = 'Main street';
        $addressDto->number = '5B';
        $addressDto->city = 'Seattle';
        $addressDto->zipCode = '123456';
        $addressDto->province = Province::Antwerp;

        $friendDto = new PersonDataTransferObject();
        $friendDto->firstName = 'Luke';
        $friendDto->lastName = 'Skywalker';
        $friendDto->address = $addressDto;
        $friendDto->bestFriend = null;
        $friendDto->friends = new ArrayCollection();

        $dto = new PersonDataTransferObject();
        $dto->firstName = 'John';
        $dto->lastName = 'Doe';
        $dto->address = $addressDto;
        $dto->bestFriend = $friendDto;
        $dto->friends = new ArrayCollection();

        /** @var Person $entity */
        $entity = $dto->create();

        $this->assertInstanceOf(Person::class, $entity);
        $this->assertEquals('John', $entity->getFirstName());
        $this->assertEquals('Doe', $entity->getLastName());
        $this->assertInstanceOf(Address::class, $entity->getAddress());
        $this->assertEquals('Main street', $entity->getAddress()->getStreet());
        $this->assertEquals('5B', $entity->getAddress()->getNumber());
        $this->assertEquals('Seattle', $entity->getAddress()->getCity());
        $this->assertEquals('123456', $entity->getAddress()->getZipCode());
        $this->assertEquals(Province::Antwerp, $entity->getAddress()->getProvince());
        $this->assertInstanceOf(Person::class, $entity->getBestFriend());
        $this->assertInstanceOf(Address::class, $entity->getBestFriend()->getAddress());
    }

    public function testConstructor(): void
    {
        $friend = new Person(
            'Luke',
            'Skywalker',
            new Address(
                'Main street',
                '5B',
                'Seattle',
                '123456',
                Province::Antwerp
            ),
            null
        );

        $entity = new Person(
            'John',
            'Doe',
            new Address(
                'Main street',
                '5B',
                'Seattle',
                '123456',
                Province::Antwerp
            ),
            $friend
        );

        $dto = PersonDataTransferObject::from($entity);
        $this->assertInstanceOf(AddressDataTransferObject::class, $dto->address);
        $this->assertInstanceOf(PersonDataTransferObject::class, $dto->bestFriend);
        $this->assertNull($dto->bestFriend->bestFriend);
    }

    public function testUpdate(): void
    {
        $friend = new Person(
            'Luke',
            'Skywalker',
            new Address(
                'Main street',
                '5B',
                'Seattle',
                '123456',
                Province::Antwerp
            ),
            null
        );

        $entity = new Person(
            'John',
            'Doe',
            new Address(
                'Main street',
                '5B',
                'Seattle',
                '123456',
                Province::Antwerp
            ),
            $friend
        );

        $dto = PersonDataTransferObject::from($entity);

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
}

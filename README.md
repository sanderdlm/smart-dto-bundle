SmartDtoBundle
===============

The SmartDtoBundle makes it faster to work with data transfer objects and Doctrine entities in a Symfony application, by adding a touch of magic to your DTOs. 

Features:
* Automatic hydration of DTO objects from an entity
* Creating new instances of entities from a DTO
* Updating existing entities through a DTO

Installation
------------

With [composer](https://getcomposer.org), require:

`composer require dreadnip/smart-dto-bundle`

Usage
-----

The bundle has two main components:

- The `MapsTo` attribute, which is placed on DataTransferObjects to specify which Entity corresponds to that DTO.
- The `AbstractDataTransferObject` class, which acts as a base that all your DTOs extend from, and has some magic methods to handle the movement of data into and out of your DTOs. 

### Example set-up

Person entity:
```php
<?php

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column]
    private string $firstName;

    #[ORM\Column]
    private string $lastName;

    #[ORM\Embedded(class: Address::class)]
    private Address $address;

    public function __construct(
        string $firstName,
        string $lastName,
        Address $address,
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->address = $address;
    }

    public function update(
        string $firstName,
        string $lastName,
        Address $address,
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->address = $address;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }
}

```
PersonDataTransferObject:
```php
<?php

#[MapsTo(entity:Person::class)]
class PersonDataTransferObject extends AbstractDataTransferObject
{
    #[Assert\NotBlank]
    public ?string $firstName = null;

    #[Assert\NotBlank]
    public ?string $lastName = null;

    #[Assert\NotBlank]
    public ?AddressDataTransferObject $address = null;
}
```

## Usage

If nothing is passed to the constructor, you can use it as blank form data.
```php
$createPerson = new PersonDataTransferObject();
```
If you call the create method on the DTO, an attempt is made to generate a new entity instance. In short, the constructor of the entity class in the `MapsTo` attribute is called with the data that is currently inside the DTO.
```php
$person = $personDataTransferObject->create();
````
If an existing entity is passed to the named constructor, the DTO will be hydrated with the entities' data. Any values already set in the DTO (for example in the constructor), will be skipped by the hydration step. You can then use this as form data for an update/edit step.
```php
$existingPerson = $personRepository->find(1);
$updatePerson = PersonDataTransferObject::from($existingPerson);
```
If you call the update method of the DTO, an attempt is made to update the original entity (which you passed to the named constructor), with the values currently in the DTO. This only works if your entity has an update method like the Person entity above. You can then flush the entity to persist the updates values.
```php
$personDataTransferObject->update();
```
You can extend the DTO and still maintain the same functionality. You don't have to repeat the attribute. This way you can create commands and handlers that make sense.
```php
<?php

class CreatePerson extends PersonDataTransferObject
{
}
```
Both the create and update methods work recursively. Nested DTOs and entities will behave exactly like the base DTO.

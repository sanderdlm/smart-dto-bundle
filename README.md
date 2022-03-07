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

There is no explicit mapping configuration because the bundle uses reflection and attempts to work with any object you throw at it. It will complain when properties are not found, or mismatched objects are passed.
### Example set-up
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

## Usage
### Entity -> DTO
The hydration of the DTOs is done through a static named constructor called `fromEntity` on the base AbstractDataTransferObject. Using this constructor will hydrate all public properties of the DTO you call it on with matching properties from the entity you pass to the constructor. Any values already set in the DTO (for example in the constructor), will be skipped by the hydration step.
```php
$existingPerson = $personRepository->find(1);
$updatePerson = PersonDataTransferObject::fromEntity($existingPerson);
```
### DTO -> Entity
If you call the create method on the DTO, an attempt is made to generate a new entity instance. In short, the constructor of the entity class in the `MapsTo` attribute is called with the data that is currently inside the DTO.
```php
$person = $personDataTransferObject->create();
````
If you call the update method of the DTO, an attempt is made to update the original entity (which you passed to the named constructor), with the values currently in the DTO. This only works if your entity has an update method like the Person entity above. 
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

## Limitations
This bundle is an attempt to save time. Most people would not agree with the shortcuts it takes, but it fits certain use-cases. 
* The hydration uses the Symfony PropertyInfo and [PropertyAccessor](https://symfony.com/doc/current/components/property_access.html#reading-from-objects) components. If a property of the DTO is not found on the entity, an error will be thrown.
* The create/update of the entity uses basic reflection to call the constructor and update method of the entity. This isn't that robust, but works well enough.
* Since the create/update methods work with any entity that you enter in the attribute, the return type is a generic `object`. Your IDE will not know which entity is returned and you`ll have to use inline var docblocks to handle this yourself.
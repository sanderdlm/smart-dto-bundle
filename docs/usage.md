# Usage

The bundle has two main components:

- The `MapsTo` attribute, which is placed on Doctrine entities to specify which DTO corresponds to that entity.
- The `DataMapperTrait` trait, which includes a static constructor `from` that you can use to hydrate your DTO from a specific entity.

## Example set-up
PersonDataTransferObject:
```php
<?php

use Dreadnip\SmartDtoBundle\DataMapperTrait;

class PersonDataTransferObject
{
    use DataMapperTrait;
        
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
#[MapsTo(dataTransferObject: PersonDataTransferObject::class)]
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
Each DTO that you use the `DataMapper` trait in will get access to the `from` method. The parameter for this method is a Doctrine entity. 

Once called, a new instance of the DTO will be created and hydrated with any possible values found inside the entity.

```php
$existingPerson = $personRepository->find(1);
$updatePerson = PersonDataTransferObject::fromEntity($existingPerson);
```
You can then pass this DTO to a Symfony form and expose it to your user. When the form submits, you can use the DTO to either update an existing entity or make a new one.

Each DTO that gets hydrated from an entity keeps the original entity as a reference in the `source` property. You can access this property through the `getSource` getter.
Some notes:
* Scalar values will simply be passed on.
* Built-in object like DateTime and enums will be passed on
* If the value for a property is an entity that has a DTO, it will be converted into a DTO as well
* If your entity has a collection of entities that also match to a DTO, the entire collection will be converted

You can extend the DTO and still maintain the same functionality. This way you can create commands and handlers that make sense.
```php
<?php

class UpdatePerson extends PersonDataTransferObject
{
}
```
Nested DTOs and entities will behave exactly like the base DTO.

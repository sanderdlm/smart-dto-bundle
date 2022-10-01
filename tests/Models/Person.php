<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Test\Models;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dreadnip\SmartDtoBundle\Attribute\MapsTo;

#[ORM\Entity]
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

    #[ORM\OneToOne(targetEntity: Person::class)]
    private ?Person $bestFriend;

    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy:"buddies")]
    private Collection $friends;

    #[ORM\Column(nullable: true)]
    private ?DateTime $lastCheckIn;

    public function __construct(
        string $firstName,
        string $lastName,
        Address $address,
        ?Person $bestFriend = null,
        ?DateTime $lastCheckIn = null
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->address = $address;
        $this->bestFriend = $bestFriend;
        $this->lastCheckIn = $lastCheckIn;
        $this->friends = new ArrayCollection();
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

    public function getBestFriend(): ?Person
    {
        return $this->bestFriend;
    }

    public function getFriends(): ArrayCollection|Collection
    {
        return $this->friends;
    }

    public function addFriend(Person $friend): void
    {
        $this->friends->add($friend);
    }

    public function getLastCheckIn(): ?DateTime
    {
        return $this->lastCheckIn;
    }
}

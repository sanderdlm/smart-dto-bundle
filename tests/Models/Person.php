<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Test\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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

    public function __construct(
        string $firstName,
        string $lastName,
        Address $address,
        ?Person $bestFriend
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->address = $address;
        $this->bestFriend = $bestFriend;
        $this->friends = new ArrayCollection();
    }

    public function update(
        string $firstName,
        string $lastName,
        Address $address,
        ?Person $bestFriend,
        Collection $friends
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->address = $address;
        $this->bestFriend = $bestFriend;
        $this->friends = $friends;
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
}

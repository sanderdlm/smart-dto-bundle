<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Test\Models;

use Doctrine\ORM\Mapping as ORM;
use Dreadnip\SmartDtoBundle\Attribute\MapsTo;

#[ORM\Embeddable]
#[MapsTo(dataTransferObject: AddressDataTransferObject::class)]
class Address
{
    #[ORM\Column]
    private string $street;

    #[ORM\Column]
    private string $number;

    #[ORM\Column]
    private string $zipCode;

    #[ORM\Column]
    private string $city;

    #[ORM\Column(enumType: Province::class)]
    private Province $province;

    public function __construct(
        string $street,
        string $number,
        string $zipCode,
        string $city,
        Province $province
    ) {
        $this->street = $street;
        $this->number = $number;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->province = $province;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getProvince(): Province
    {
        return $this->province;
    }
}

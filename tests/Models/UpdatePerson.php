<?php

namespace Dreadnip\SmartDtoBundle\Test\Models;

class UpdatePerson extends PersonDataTransferObject
{
    public function __construct()
    {
        $this->firstName = 'Jesus';
    }
}

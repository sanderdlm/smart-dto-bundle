<?php

namespace Dreadnip\SmartDtoBundle\Test\Models;

class UpdatePersonWithConstructor extends PersonDataTransferObject
{
    public function __construct()
    {
        $this->firstName = 'Jesus';
    }
}

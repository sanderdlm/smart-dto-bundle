<?php
// phpcs:ignoreFile
declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Test\Models;

enum Province: string
{
    case WestFlanders = 'west-vlaanderen';
    case EastFlanders = 'oost-vlaanderen';
    case Antwerp = 'antwerpen';
    case FlemishBrabant = 'vlaams brabant';
    case Limburg = 'limburg';
}

<?php

namespace DpTest\DeskPRO\Application\Entity;

use Application\DeskPRO\Entity\Person;
use DpTest\DeskProTestCase;

/**
 * Class PersonTest.
 */
class PersonTest extends DeskProTestCase
{
    /**
     * @testWith ["Tianna Cole", "Tianna", "Cole"]
     *           ["孝博　向吉", "孝博", "向吉"]
     *
     * @param string $name
     * @param string $expFirstName
     * @param string $expLastName
     */
    public function testSetName_checkSplit($name, $expFirstName, $expLastName)
    {
        $person = new Person();
        $person->setName($name);

        $this->assertEquals($expFirstName, $person->getFirstName());
        $this->assertEquals($expLastName, $person->getLastName());
    }
}

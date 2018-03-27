<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Person;

use Application\DeskPRO\Entity\Person;

class LoginRequiredException extends \RuntimeException
{
    /**
     * @var Person
     */
    private $person;

    public function __construct(Person $person)
    {
        $this->person = $person;
        parent::__construct('login is required to perform this action');
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }
}

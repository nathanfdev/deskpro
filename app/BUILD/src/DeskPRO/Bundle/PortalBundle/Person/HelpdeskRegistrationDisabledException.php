<?php

namespace DeskPRO\Bundle\PortalBundle\Person;

use Application\DeskPRO\Entity\Person;

/**
 * Class HelpdeskRegistrationDisabledException.
 */
class HelpdeskRegistrationDisabledException extends \RuntimeException
{
    /**
     * @var Person
     */
    private $person;

    /**
     * Constructor.
     *
     * @param Person $person
     */
    public function __construct(Person $person = null)
    {
        parent::__construct('Helpdesk registration has been disabled');
        $this->person = $person;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }
}

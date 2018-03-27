<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Person\Events;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use Symfony\Component\EventDispatcher\Event;

class PersonCreateEvent extends Event
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext
     */
    private $context;

    public function __construct(Person $person, CreatePersonContext $context)
    {
        $this->person  = $person;
        $this->context = $context;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     */
    public function setPerson($person)
    {
        $this->person = $person;
    }

    /**
     * @return CreatePersonContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param CreatePersonContext $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}

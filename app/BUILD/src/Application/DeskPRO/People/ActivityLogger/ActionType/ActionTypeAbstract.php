<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;

abstract class ActionTypeAbstract implements PersonContextInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPersonContext()
    {
        return $this->person;
    }

    /**
     * Get a plain array of details that'll be stored in the databaes.
     *
     * @return array
     */
    abstract public function getDetails();
}

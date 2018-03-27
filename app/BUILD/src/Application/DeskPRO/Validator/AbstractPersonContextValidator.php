<?php

/**
 * Orb.
 */

namespace Application\DeskPRO\Validator;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;
use Orb\Validator\AbstractValidator;

abstract class AbstractPersonContextValidator extends AbstractValidator implements PersonContextInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    public function init()
    {
        $this->setPersonContext($this->getOption('person_context'));
    }

    /**
     * @param \Application\DeskPRO\Entity\Person|null $person_context
     */
    public function setPersonContext(Person $person_context = null)
    {
        $this->person_context = $person_context;
    }

    /**
     * @return bool
     */
    public function hasPerson()
    {
        return $this->person_context !== null;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPerson()
    {
        return $this->person_context;
    }
}

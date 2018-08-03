<?php

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\Entity\Person;

class MergeUndone extends ActionTypeAbstract
{
    /**
     * Person that has been recreated.
     *
     * @var Person
     */
    protected $otherPerson;

    /**
     * @param Person $person
     * @param Person $otherPerson
     */
    public function __construct(Person $person, Person $otherPerson)
    {
        $this->person      = $person;
        $this->otherPerson = $otherPerson;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return [
            'other_person_id'   => $this->otherPerson->getId(),
            'other_person_name' => $this->otherPerson->getDisplayName(),
        ];
    }
}

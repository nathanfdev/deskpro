<?php

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\Entity\Person;

class MergeUndone extends ActionTypeAbstract
{
    /**
     * Base Person to whom merge undo was applied.
     *
     * @var Person
     */
    protected $basePerson;

    /**
     * Person that has been recreated.
     *
     * @var Person
     */
    protected $otherPerson;

    /**
     * @param Person $person
     * @param Person $basePerson
     * @param Person $otherPerson
     */
    public function __construct(Person $person, Person $basePerson, Person $otherPerson)
    {
        // Person to whom this action is connected
        $this->person = $person;

        $this->basePerson  = $basePerson;
        $this->otherPerson = $otherPerson;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return [
            'base_person_id'    => $this->basePerson->getId(),
            'base_person_name'  => $this->basePerson->getDisplayName(),
            'other_person_id'   => $this->otherPerson->getId(),
            'other_person_name' => $this->otherPerson->getDisplayName(),
            // This action applied to both BasePerson and OtherPerson
            // save this mark to properly display information in UI
            'side' => $this->person->getId() === $this->basePerson->getId() ? 'base' : 'other',
        ];
    }
}

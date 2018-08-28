<?php

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\Entity\Person;

class Merged extends ActionTypeAbstract
{
    /**
     * Merged person backup Id.
     * Used by PersonMerge\MergeBackup to lookup backup and restore Persons from it.
     *
     * @var int
     */
    protected $backupId;

    /**
     * @var Person
     */
    protected $otherPerson;

    /**
     * @param Person   $person
     * @param int|null $backupId
     */
    public function __construct(Person $person, Person $otherPerson, $backupId)
    {
        $this->person      = $person;
        $this->backupId    = $backupId;
        $this->otherPerson = $otherPerson;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        $details = [
            'other_person_id'   => $this->otherPerson->getId(),
            'other_person_name' => $this->otherPerson->getDisplayName(),
        ];
        if ($this->backupId) {
            $details['datastore_merge_backup_id'] = $this->backupId;
        }

        return $details;
    }
}

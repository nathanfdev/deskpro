<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\Entity\Person;

/**
 * The personlistener listens for changes to a ticket, and then runs inspections once the changes
 * are committed.
 */
class PersonChangeTracker extends \Application\DeskPRO\Domain\ChangeTracker
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var bool
     */
    protected $running = false;

    public function __construct(Person $person)
    {
        $this->entity = $person;
        $this->person = $person;
    }

    /**
     * Get the person.
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Was the person new (just created?).
     *
     * @return bool
     */
    public function isNewPerson()
    {
        return $this->person->isNewPerson();
    }

    public function propertyChanged($sender, $prop, $old_val, $new_val)
    {
        if (in_array($prop, ['notes'])) {
            $this->recordMultiPropertyChanged($prop, $old_val, $new_val);
        } else {
            $this->recordPropertyChanged($prop, $old_val, $new_val);
        }
    }

    public function preSave()
    {
        if ($this->running) {
            return;
        }
        $this->running = true;

        $this->running = false;
    }

    /**
     * Notify all listeners that changes to the person have been committed.
     */
    public function done()
    {
    }

    public function clear()
    {
        $this->person = null;
        $this->entity = null;
    }
}

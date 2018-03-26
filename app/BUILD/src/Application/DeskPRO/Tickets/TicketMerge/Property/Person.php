<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketMerge\Property;

/**
 * This adds the ability to change the user owner of the ticket. But also
 * allows the option of adding the new user as a participant.
 */
class Person extends PropertyAbstract
{
    public function merge()
    {
        if ($this->strategy == self::STRATEGY_RIGHT and $this->ticket->person != $this->other_ticket->person) {
            $old_person = $this->ticket->person;

            $this->ticket->person       = $this->other_ticket->person;
            $this->ticket->person_email = $this->other_ticket->person_email;
            $this->ticket->organization = $this->other_ticket->organization;

            if ($this->getStrategyOption('add_follower') and $old_person != $this->ticket->person) {
                $this->ticket->addParticipantPerson($old_person);
            }
        }
    }
}

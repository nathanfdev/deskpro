<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\Entity\Person;

class Registered extends ActionTypeAbstract
{
    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    public function getDetails()
    {
        $name  = $this->person->getDisplayName(false);
        $email = $this->person->getEmailAddress();

        if (!$name || !$email) {
            return [];
        }

        return [
            'name'  => $name,
            'email' => $email,
        ];
    }
}

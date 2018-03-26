<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionChecker;

class GeneralChecker extends AbstractChecker
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    public function canSetSignature()
    {
        if (!$this->person->is_agent) {
            return false;
        }

        return $this->person->hasPerm('agent_general.signature');
    }

    public function canSetPicture()
    {
        if (!$this->person->is_agent) {
            return true;
        }

        return $this->person->hasPerm('agent_general.picture');
    }
}

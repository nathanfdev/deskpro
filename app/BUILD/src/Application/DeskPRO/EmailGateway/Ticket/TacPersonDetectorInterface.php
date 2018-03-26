<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;

/**
 * A detector class may also be able to detect people from a TAC code.
 */
interface TacPersonDetectorInterface
{
    /**
     * @param AbstractReader $reader
     *
     * @return \Application\DeskPRO\Entity\Person|null
     */
    public function findTacPerson(AbstractReader $reader);
}

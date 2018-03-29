<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Ticket;

/**
 * A ticket detector scans an email to try and detect if an email
 * is in reply to an existing ticket.
 */
interface TicketDetectorInterface
{
    /**
     * Should return a ticket if one was found. If no ticket is found, return null.
     *
     * @param \Application\DeskPRO\EmailGateway\Reader\AbstractReader $reader
     *
     * @return \Application\DeskPRO\Entity\Ticket
     */
    public function findExistingTicket(AbstractReader $reader);

    /**
     * If there was a ticket found and the email is a reply, then
     * this shuold discover who is making the reply.
     *
     * This is important because we might not know the From address, but if
     * the detector has some other way of knowing who the person is,
     * then we can still associate accounts properly.
     * (ex multiple participants might each get a different code etc)
     *
     * @param \Application\DeskPRO\Entity\Ticket                      $ticket
     * @param \Application\DeskPRO\EmailGateway\Reader\AbstractReader $reader
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function findExistingPerson(Ticket $ticket, AbstractReader $reader);

    /**
     * If a ticket is found but a person isn't, should we add the new email address
     * as a new CC or should we deny the message?
     *
     * @param \Application\DeskPRO\Entity\Ticket                      $ticket
     * @param \Application\DeskPRO\EmailGateway\Reader\AbstractReader $reader
     *
     * @return bool
     */
    public function canAddUnknownPerson(Ticket $ticket, AbstractReader $reader);
}

<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Ticket;

/**
 * Detects a ticket based off of REF codes in the subject.
 */
class SubjectRefMatchDetector implements TicketDetectorInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $_found_person = null;

    /**
     * @var int
     */
    protected $_time_cutoff = 0;

    /**
     * @param int $time_cutoff Max age of a ticket before the subject match wont work
     */
    public function __construct($time_cutoff = 604800 /* 7 days */)
    {
        $this->_time_cutoff = date('Y-m-d H:i:s', time() - $time_cutoff);
    }

    /**
     * {@inheritdoc}
     */
    public function findExistingTicket(AbstractReader $reader)
    {
        $this->_found_person = null;

        $subject = trim($reader->getSubject()->subject);

        $ticket_refs = App::getSystemService('RefGenerator')->extractRefs($subject);
        if (!$ticket_refs) {
            return;
        }

        foreach ($ticket_refs as $ref) {
            try {
                $ticket = App::getEntityRepository('DeskPRO:Ticket')->findOneByRef($ref);
            } catch (\Exception $e) {
                continue;
            }

            if (!$ticket) {
                continue;
            }

            if (!$ticket->isArchived() && $p = $ticket->findUserByEmail($reader->getFromAddress()->getEmail())) {
                $this->_found_person = $p;

                return $ticket;
            }
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function findExistingPerson(Ticket $ticket, AbstractReader $reader)
    {
        if ($this->_found_person) {
            return $this->_found_person;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function canAddUnknownPerson(Ticket $ticket, AbstractReader $reader)
    {
        return false;
    }
}

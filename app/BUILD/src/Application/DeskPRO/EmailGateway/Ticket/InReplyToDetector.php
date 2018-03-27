<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Ticket;

/**
 * Detects a ticket based off of In-Reply-To field and the From: must
 * be from a user we know about.
 *
 * @see \Application\DeskPRO\Entity\TicketAccessCode
 */
class InReplyToDetector implements TicketDetectorInterface, PublicTacAware
{
    /**
     * @var \Application\DeskPRO\Entity\TicketAccessCode
     */
    protected $_found_person = null;

    /**
     * @var bool
     */
    protected $publicTac = false;

    /**
     * @param AbstractReader $reader
     *
     * @return Ticket|null
     */
    public function findExistingTicket(AbstractReader $reader)
    {
        $this->publicTac     = false;
        $this->_found_person = null;

        //------------------------------
        // Fetch message Ids from headers
        //------------------------------

        $search_text = [];

        // In-Reply-To should have the direct message
        // being replied to
        $in_reply_to = $reader->getHeader('In-Reply-To');
        if ($in_reply_to) {
            foreach ($in_reply_to->getAllParts() as $part) {
                $search_text[] = $part;
            }
        }

        // References may have other messages in a thread,
        // so also a good place to look for the TAC
        $references = $reader->getHeader('References');
        if ($references) {
            foreach ($references->getAllParts() as $part) {
                $search_text[] = $part;
            }
        }

        $search_text = implode(' ', $search_text);

        //------------------------------
        // Try to find TAC
        //------------------------------

        $authcode_min_len = Ticket::TAC_AUTHCODE_LEN + 1;
        $authcode_max_len = Ticket::TAC_AUTHCODE_LEN_MAX;

        $matches = null;
        if (preg_match_all('#(?<!P)TAC\-([A-Z0-9]{'.$authcode_min_len.','.$authcode_max_len.'})\.#i', $search_text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $tac = App::getEntityRepository('DeskPRO:TicketAccessCode')->findByAccessCode($m[1]);
                if (!$tac) {
                    continue;
                }

                $ticket = $tac->ticket;
                if (!$ticket->isArchived()) {
                    $this->_found_person = $tac->person;

                    return $ticket;
                }
            }
        }

        //------------------------------
        // Try to find PTAC
        //------------------------------

        $matches = null;
        if (preg_match_all('#(?:PTAC|TICKET)\-([A-Z0-9]{'.$authcode_min_len.','.$authcode_max_len.'})\.#i', $search_text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $ticket = App::getEntityRepository('DeskPRO:Ticket')->getByAccessCode($m[1]);

                if ($ticket) {
                    $this->publicTac = true;
                }

                if ($ticket && !$ticket->isArchived()) {
                    $this->_found_person = $ticket->findUserByEmail($reader->getFromAddress()->email);

                    return $ticket;
                }
            }
        }

        return;
    }

    /**
     * @param Ticket         $ticket
     * @param AbstractReader $reader
     *
     * @return \Application\DeskPRO\Entity\Person|\Application\DeskPRO\Entity\TicketAccessCode|null
     */
    public function findExistingPerson(Ticket $ticket, AbstractReader $reader)
    {
        if ($this->_found_person) {
            return $this->_found_person;
        }

        return;
    }

    /**
     * Add unknown users, the reply code in the address is the PTAC
     * so basically a passowrd.
     *
     * @param Ticket         $ticket
     * @param AbstractReader $reader
     *
     * @return bool
     */
    public function canAddUnknownPerson(Ticket $ticket, AbstractReader $reader)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isPublicTac()
    {
        return $this->publicTac;
    }
}

<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Ticket;
use Orb\Log\Loggable;
use Orb\Log\Logger;

/**
 * Detects a ticket based off of codes in the subject or body.
 *
 * We look for (#AAAAA) in either the subject or body.
 * These are access codes that we can use to find a corresponding ticket and user.
 *
 * @see \Application\DeskPRO\Entity\TicketAccessCode
 */
class CodeTicketDetector implements TicketDetectorInterface, BounceAwareInterface, TacPersonDetectorInterface, Loggable, PublicTacAware
{
    /**
     * @var \Application\DeskPRO\Entity\TicketAccessCode
     */
    protected $_found_tac = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $_found_person = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $_tac_person = null;

    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $is_bounce_mode = false;

    /**
     * @var bool
     */
    protected $publicTac = false;

    /**
     * Enable bounce mode if the message is or is suspected ot be a bounced message.
     * This will look for PTAC/TAC 'headers' in the body text.
     */
    public function enableBouncedMode()
    {
        $this->is_bounce_mode = true;
    }

    /**
     * {@inheritdoc}
     */
    public function findExistingTicket(AbstractReader $reader)
    {
        $this->publicTac = false;
        $this->getLogger()->logDebug('[CodeTicketDetector] Finding ticket');

        $this->_found_person = null;

        $search_text   = [];
        $search_text[] = $reader->getSubject()->subject;
        $search_text[] = $reader->getBodyText()->getBody();
        $search_text[] = $reader->getBodyHtml()->getBody();

        $check_headers = [];
        if ($reader->getHeader('In-Reply-To')) {
            foreach ($reader->getHeader('In-Reply-To')->getAllParts() as $part) {
                $check_headers[] = $part;
            }
        }
        if ($reader->getHeader('References')) {
            foreach ($reader->getHeader('References')->getAllParts() as $part) {
                $check_headers[] = $part;
            }
        }

        // If its a bounced message, then the headers might be included in readable-text
        if ($this->is_bounce_mode) {
            $body = $reader->getBodyText()->getBody();
            if (!$body) {
                $body = strip_tags($reader->getBodyHtml()->getBody());
            }

            $m = null;
            if (preg_match_all('#(?:PTAC|TAC|TICKET)\-([A-Za-z0-9]+)\.#', $body, $m, \PREG_SET_ORDER)) {
                foreach ($m as $match) {
                    $this->getLogger()->logDebug('[CodeTicketDetector] Found PTAC in body-headers: '.$match[1]);
                    $search_text[] = '(#'.$match[1].')';
                }
            }
        }

        // Add them to search text so below code will parse them out and treat them the same
        foreach ($check_headers as $header) {
            $m = null;
            if (preg_match('#(?:PTAC|TAC|TICKET)\-([A-Za-z0-9]+)\.#', $header, $m)) {
                $this->getLogger()->logDebug('[CodeTicketDetector] Found PTAC in body-headers: '.$m[1]);
                $search_text[] = '(#'.$m[1].')';
            }
        }

        $search_text = array_unique($search_text);
        $search_text = implode(' ', $search_text);

        $authcode_min_len = Ticket::TAC_AUTHCODE_LEN + 1;
        $authcode_max_len = Ticket::TAC_AUTHCODE_LEN_MAX;

        //------------------------------
        // TAC
        //------------------------------

        // TACs must be checked first
        // They will be more specific (specific person/agent)
        // which matters when we need to detect context later.

        // E.g., if this is an agent TAC, then we know its an agent context
        // even if its a plaintext message. Otherwise, we have to assume user context
        // because of a public TAC

        $already_checked = [];

        $matches = null;
        if (preg_match_all('/\(#([A-Z0-9]{'.$authcode_min_len.','.$authcode_max_len.'})\)/', $search_text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (isset($already_checked[$m[1]])) {
                    continue;
                }
                $already_checked[$m[1]] = true;

                $this->getLogger()->logDebug("[CodeTicketDetector] Checking code that looks like TAC: {$m[1]}");

                $tac = App::getEntityRepository('DeskPRO:TicketAccessCode')->getTacArrayFromAccessCode($m[1]);
                if (!$tac) {
                    $this->getLogger()->logDebug('[CodeTicketDetector] -- Invalid code');
                    continue;
                }

                $this->getLogger()->logDebug('[CodeTicketDetector] -- Valid code');

                $ticket = App::getEntityRepository('DeskPRO:Ticket')->find($tac['ticket_id']);
                if ($ticket && !$ticket->isArchived()) {
                    // The person we have from the email address
                    $this->_found_person = $ticket->findUserByEmail($reader->getFromAddress()->email);

                    if (!$this->_found_person) {
                        $this->_found_person = $ticket->findAgentByEmail($reader->getFromAddress()->email);
                    }

                    // The person who should own this tac
                    $this->_tac_person = App::getEntityRepository('DeskPRO:Person')->find($tac['person_id']);

                    if ($this->_found_person) {
                        $this->getLogger()->logDebug("[CodeTicketDetector] -- Matched ticket {$ticket->id} with person already on ticket: {$this->_found_person->id} {$this->_found_person->getDisplayContact()}");
                    } else {
                        $this->getLogger()->logDebug("[CodeTicketDetector] -- Matched ticket {$ticket->id} with person not already on ticket");
                    }

                    return $ticket;
                }
            }
        }

        //------------------------------
        // PTAC
        //------------------------------

        // Reset the already checked array we build during tac checking,
        // we check the codes again for ptacs now
        $already_checked = [];

        $matches = null;
        if (preg_match_all('/\(#([A-Z0-9]{'.$authcode_min_len.','.$authcode_max_len.'})\)/', $search_text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (isset($already_checked[$m[1]])) {
                    continue;
                }
                $already_checked[$m[1]] = true;

                $this->getLogger()->logDebug("[CodeTicketDetector] Checking code that looks like PTAC: {$m[1]}");

                $ticket = App::getEntityRepository('DeskPRO:Ticket')->getByAccessCode($m[1]);

                if ($ticket) {
                    $this->publicTac = true;
                }

                if ($ticket && !$ticket->isArchived()) {
                    $this->_found_person = $ticket->findUserByEmail($reader->getFromAddress()->email);

                    if ($this->_found_person) {
                        $this->getLogger()->logDebug("[CodeTicketDetector] -- Matched ticket {$ticket->id} with person already on ticket: {$this->_found_person->id} {$this->_found_person->getDisplayContact()}");
                    } else {
                        $this->getLogger()->logDebug("[CodeTicketDetector] -- Matched ticket {$ticket->id} with person not already on ticket");
                    }

                    return $ticket;
                }

                $this->getLogger()->logDebug('[CodeTicketDetector] -- Invalid code');
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
    public function findTacPerson(AbstractReader $reader)
    {
        return $this->_tac_person;
    }

    /**
     * {@inheritdoc}
     */
    public function canAddUnknownPerson(Ticket $ticket, AbstractReader $reader)
    {
        return true;
    }

    /**
     * Set the logger.
     *
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    /**
     * @return bool
     */
    public function isPublicTac()
    {
        return $this->publicTac;
    }
}

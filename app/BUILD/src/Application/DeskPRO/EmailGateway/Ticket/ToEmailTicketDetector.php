<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Ticket;
use Orb\Util\Strings;

/**
 * Detects a ticket based off of the code in the TO address that
 * the email was sent to.
 *
 * This is used with a catch-all email address. When a user replies to a notification,
 * we automatically detect the ticket based on the address: ticket-ABIEUJF@example.com
 *
 * @see \Application\DeskPRO\Entity\TicketAccessCode
 */
class ToEmailTicketDetector implements TicketDetectorInterface, PublicTacAware
{
    /**
     * The regex to match.
     *
     * @var string
     */
    protected $account_pattern;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $_found_person = null;

    /**
     * @var bool
     */
    protected $publicTac = false;

    /**
     * $account_pattern needs to be an email address with the special token TICKET_CODE
     * in it to denote the position of the ticket code.
     *
     * For example:
     * <code>
     * $detector = new ToEmailTicketDetector('ticket-TAC@example.com');
     * </code>
     *
     * @param string $account_pattern The pattern with the special token TICKET_CODE in it
     */
    public function __construct($account_pattern)
    {
        $account_pattern = preg_quote($account_pattern, '#');

        $authcode_min_len = Ticket::TAC_AUTHCODE_LEN + 1;
        $authcode_max_len = Ticket::TAC_AUTHCODE_LEN_MAX;

        $account_pattern = str_replace('TAC', '(?P<auth>[A-Z0-9]{'.$authcode_min_len.','.$authcode_max_len.'})', $account_pattern);

        $this->account_pattern = '#^'.$account_pattern.'#$';
    }

    /**
     * {@inheritdoc}
     */
    public function findExistingTicket(AbstractReader $reader)
    {
        $this->publicTac = false;
        $search_addr     = [];
        foreach ($reader->getToAddresses() as $addr) {
            $search_addr[] = $addr->email;
        }
        foreach ($reader->getCcAddresses() as $addr) {
            $search_addr[] = $addr->email;
        }

        // Easier to run regex on all at once
        $search_addr = ' '.implode(' ', $search_addr).' ';

        $match_ptac = Strings::extractRegexMatch($this->account_pattern, $search_addr, 'auth');
        if (!$match_ptac) {
            return;
        }

        //------------------------------
        // Try to find the ticket and user now
        //------------------------------

        $ticket = App::getEntityRepository('DeskPRO:Ticket')->getByAccessCode($match_ptac);

        if ($ticket) {
            $this->publicTac = true;
        }

        if ($ticket && !$ticket->isArchived()) {
            $this->_found_person = $ticket->findUserByEmail($reader->getFromAddress()->email);

            return $ticket;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function findExistingPerson(Ticket $ticket, AbstractReader $reader)
    {
        if ($this->_found_tac) {
            return $this->_found_tac->person;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function canAddUnknownPerson(Ticket $ticket, AbstractReader $reader)
    {
        return true;
    }

    public function isPublicTac()
    {
        return $this->publicTac;
    }
}

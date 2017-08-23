<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Ticket;
use Orb\Log\Loggable;
use Orb\Log\Logger;
use Orb\Util\Arrays;
use Orb\Util\Strings;

/**
 * Detects a ticket based off of a common subject and From email address.
 * For example "RE: Something".
 *
 * @see \Application\DeskPRO\Entity\TicketAccessCode
 */
class SubjectMatchDetector implements TicketDetectorInterface, BounceAwareInterface, Loggable
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
    protected $enable_exact_subject = false;

    protected $enable_same_account = null;

    /**
     * Enable bounce mode if the message is or is suspected ot be a bounced message.
     * This will look for PTAC/TAC 'headers' in the body text.
     */
    public function enableBouncedMode()
    {
        $this->is_bounce_mode = true;
    }

    /**
     * @param EmailAccount $account
     */
    public function enableSameAccountSubjectMatching(EmailAccount $account)
    {
        $this->enable_same_account = $account;
    }

    /**
     * When enabled, this will try to match exact subjects. Usually we only try
     * matching when there's a reply prefix (like RE:) because that indicates it's a reply.
     * But in some cases (e.g., automated systems) you might want to connect all emails
     * with the same subject.
     *
     * Before:
     *        These match:  "I love DeskPRO" and "RE: I love DeskPRO"   -> 1 ticket with 1 reply
     *        These DONT:   "I love DeskPRO" and "I love DeskPRO"       -> 2 separate tickets
     *
     * With this setting:
     *        These match:  "I love DeskPRO" and "RE: I love DeskPRO"   -> 1 ticket with 1 reply (no change)
     *   These also match:  "I love DeskPRO" and "I love DeskPRO"       -> 1 ticket with 1 reply
     */
    public function enableExactSubjectMatching()
    {
        $this->enable_exact_subject = true;
    }

    /**
     * @param int $time_cutoff Max age of a ticket before the subject match wont work
     */
    public function __construct($time_cutoff = 7776000 /* 90 days */)
    {
        $this->_time_cutoff = date('Y-m-d H:i:s', time() - $time_cutoff);
    }

    /**
     * {@inheritdoc}
     */
    public function findExistingTicket(AbstractReader $reader)
    {
        $ticket = $this->_findExistingTicket($reader, $reader->getSubject()->getSubjectUtf8());

        if (!$ticket && $this->is_bounce_mode) {
            if ($reader->getOriginalSubject()) {
                $ticket = $this->_findExistingTicket($reader, $reader->getOriginalSubject()->getSubjectUtf8());
            }
        }

        if (!$ticket && $this->is_bounce_mode && $body = $reader->getBodyText()->getBody()) {
            $body_subject = Strings::extractRegexMatch('#^Subject:\s*(.*?)$#m', $body);
            if ($body_subject) {
                $ticket = $this->_findExistingTicket($reader, $body_subject);
            }
        }

        return $ticket;
    }

    public function _findExistingTicket(AbstractReader $reader, $subject)
    {
        $ticket = $this->_findExistingTicketStandard($reader, $subject);
        if (!$ticket) {
            $ticket = $this->_findExistingTicketExtra($reader, $subject);
        }

        return $ticket;
    }

    /**
     * Tries to find a subject by stripping off standard subject prefixes.
     *
     * @param AbstractReader $reader
     * @param $subject
     *
     * @return null|Ticket
     */
    public function _findExistingTicketStandard(AbstractReader $reader, $subject)
    {
        $this->getLogger()->logDebug('[SubjectMatchDetector] (Standard) Finding ticket');

        $this->_found_person = null;

        $subject      = trim($subject);
        $subject_orig = $subject;

        // Common prefixes
        // Also including FW|FWDxxx here to catch cases where a user uses fwd to reply to an email they just sent.
        $common_prefix_re = '#^(RE|VS|AW|SV|FW|FWD|VL|WG|FS|VB|RV|VS|TR):\s*#i';

        $extra_join  = '';
        $extra_where = '';

        if ($this->enable_same_account) {
            $this->getLogger()->logDebug("[SubjectMatchDetector] Same account requirement is enabled. Must match: {$this->enable_same_account->id}");
            $extra_join  = "LEFT JOIN email_sources ON (email_sources.object_id = tickets.id AND email_sources.object_type = 'ticket')";
            $extra_where = "AND email_sources.email_account_id = {$this->enable_same_account->id}";
        }

        $ticket_ids = [];

        if ($this->enable_exact_subject) {
            $this->getLogger()->logDebug('[SubjectMatchDetector] (Standard) Trying to find exact subject: '.$subject);
            $ticket_ids = array_merge($ticket_ids, App::getDb()->fetchAllCol("
                SELECT tickets.id
                FROM tickets
                $extra_join
                WHERE ((tickets.subject = ? OR tickets.original_subject = ?) AND tickets.date_created > ? AND tickets.status NOT IN ('archived', 'resolved', 'hidden'))
                $extra_where
                ORDER BY tickets.id DESC
                LIMIT 20
            ", [$subject, $subject, $this->_time_cutoff]));
        }

        // handle prefixes
        if (preg_match($common_prefix_re, $subject)) {
            // Strip off Re: prefix (and alternatives in some other langs)
            // The loop is so we can catch emails with multiple prefixes like RE: RE: RE:
            $last_subject = $subject_orig;

            while (true) {
                $subject_re = preg_replace($common_prefix_re, '', trim($last_subject));
                $subject_re = trim($subject_re);

                if ($subject_re == $last_subject || !$subject_re) {
                    break;
                }

                $last_subject = $subject_re;

                $this->getLogger()->logDebug('[SubjectMatchDetector] -- Trying to find subject: '.$subject_re);

                // Now lets try to find it...
                $ticket_ids = array_merge($ticket_ids, App::getDb()->fetchAllCol("
                    SELECT tickets.id
                    FROM tickets
                    $extra_join
                    WHERE ((tickets.subject = ? OR tickets.original_subject = ?) AND tickets.date_created > ? AND tickets.status NOT IN ('archived', 'resolved', 'hidden'))
                    $extra_where
                    ORDER BY tickets.id DESC
                    LIMIT 20
                ", [$subject_re, $subject_re, $this->_time_cutoff]));
            }
        }

        $ticket_ids = Arrays::removeFalsey($ticket_ids);

        if (!$ticket_ids) {
            $this->getLogger()->logDebug('[SubjectMatchDetector] -- Found nothing');

            return;
        }

        $this->getLogger()->logDebug('[SubjectMatchDetector] -- Matching tickets: '.implode(', ', $ticket_ids));

        $tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);
        $from    = $reader->getFromAddress()->getEmail();

        foreach ($tickets as $ticket) {
            if (($p = $ticket->findUserByEmail($from))) {
                $this->getLogger()->logDebug('[SubjectMatchDetector] -- Found ticket '.$ticket->id.' with user '.$p->id.' '.$p->getDisplayContact());
                $this->_found_person = $p;

                return $ticket;
            }
        }

        $this->getLogger()->logDebug('[SubjectMatchDetector] -- Could not match user email address on ticket: '.$from);

        return;
    }

    /**
     * Tries to find a subject by stripping off anything before a colon (ie non-standard prefixes).
     *
     * @param AbstractReader $reader
     * @param $subject
     *
     * @return null|Ticket
     */
    public function _findExistingTicketExtra(AbstractReader $reader, $subject)
    {
        $this->getLogger()->logDebug('[SubjectMatchDetector] (Extra) Finding ticket');

        $this->_found_person = null;

        $subject      = trim($subject);
        $subject_orig = $subject;

        if (strpos($subject, ':') === false) {
            return;
        }

        $extra_join  = '';
        $extra_where = '';

        if ($this->enable_same_account) {
            $this->getLogger()->logDebug("[SubjectMatchDetector] Same account requirement is enabled. Must match: {$this->enable_same_account->id}");
            $extra_join  = "LEFT JOIN email_sources ON (email_sources.object_id = tickets.id AND email_sources.object_type = 'ticket')";
            $extra_where = "AND email_sources.email_account_id = {$this->enable_same_account->id}";
        }

        // Strip off Re: prefix (and alternatives in some other langs)
        // The loop is so we can catch emails with multiple prefixes like RE: RE: RE:
        $last_subject = $subject_orig;
        $ticket_ids   = [];
        while (true) {
            $subject_re = preg_replace('#^[^[:punct:]\s]{2,4}:\s*#i', '', trim($last_subject));
            $subject_re = trim($subject_re);

            if ($subject_re == $last_subject || !$subject_re) {
                break;
            }

            $last_subject = $subject_re;

            $this->getLogger()->logDebug('[SubjectMatchDetector] -- Trying to find subject: '.$subject_re);

            // Now lets try to find it...
            $ticket_ids = array_merge($ticket_ids, App::getDb()->fetchAllCol("
                SELECT tickets.id
                FROM tickets
                $extra_join
                WHERE ((tickets.subject = ? OR tickets.original_subject = ?) AND tickets.date_created > ? AND tickets.status NOT IN ('archived', 'resolved', 'hidden'))
                $extra_where
                ORDER BY tickets.id DESC
                LIMIT 20
            ", [$subject_re, $subject_re, $this->_time_cutoff]));
        }

        $ticket_ids = Arrays::removeFalsey($ticket_ids);

        if (!$ticket_ids) {
            $this->getLogger()->logDebug('[SubjectMatchDetector] -- Found nothing');

            return;
        }

        $this->getLogger()->logDebug('[SubjectMatchDetector] -- Matching tickets: '.implode(', ', $ticket_ids));

        $tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);
        $from    = $reader->getFromAddress()->getEmail();

        foreach ($tickets as $ticket) {
            if (($p = $ticket->findUserByEmail($from)) || ($p = $ticket->findAgentByEmail($from))) {
                $this->getLogger()->logDebug('[SubjectMatchDetector] -- Found ticket '.$ticket->id.' with user '.$p->id);
                $this->_found_person = $p;

                return $ticket;
            }
        }

        $this->getLogger()->logDebug('[SubjectMatchDetector] -- Could not match user email address on ticket: '.$from);

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
}

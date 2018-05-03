<?php

/**
 * DeskPRO.
 *
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

/**
 * A simple struct for keeping track of some ticket email properties.
 */
class TicketIncomingEmail
{
    /**
     * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
     */
    public $reader;

    /**
     * @var \Application\DeskPRO\Entity\Ticket|null
     */
    public $ticket;

    /**
     * @var \Application\DeskPRO\Entity\Person|null
     */
    public $person;

    /**
     * @var \Application\DeskPRO\Entity\Person|null
     */
    public $tac_person;

    /**
     * @var bool
     */
    public $is_bounce;

    /**
     * @var bool
     */
    public $force_reply_cutter = false;

    /**
     * @var bool
     */
    public $force_no_reply_cutter = false;

    /**
     * @var bool
     */
    public $force_no_pattern_cutter = false;

    /**
     * @var array|null
     */
    public $reply_actions;

    /**
     * @var string
     */
    public $email_body_html;

    /**
     * @var string
     */
    public $email_body_text;

    /**
     * @var bool
     */
    public $is_dp3_reply = false;

    public $isPublicTac = false;
}

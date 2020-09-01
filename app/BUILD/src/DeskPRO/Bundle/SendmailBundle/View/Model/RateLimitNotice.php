<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use JMS\Serializer\Annotation as JMS;

class RateLimitNotice extends EmailBaseType
{
    protected $templateFile = 'emails_user:rate_limit_notice.html.twig';

    /**
     * Ticket detected
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket")
     *
     * @var Ticket
     */
    protected $ticket;

    /**
     * Email subject
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $subject;

    /**
     * Sender name or email
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * Rate limit
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $numMessages;

    /**
     * Time limit
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $timeLimit;

    /**
     * Time lock
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $timeLock;

    /**
     * The time the lime expires
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $dateLockEnd;

    public function __construct(Ticket $ticket = null, $subject, $name, $numMessages, $timeLimit, $timeLock, $dateLockEnd)
    {
        $this->ticket      = $ticket;
        $this->subject     = $subject;
        $this->name        = $name;
        $this->numMessages = $numMessages;
        $this->timeLimit   = $timeLimit;
        $this->timeLock    = $timeLock;
        $this->dateLockEnd = $dateLockEnd;
    }
}

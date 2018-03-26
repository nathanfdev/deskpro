<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use JMS\Serializer\Annotation as JMS;

class AccountDisabled extends EmailBaseType
{
    /**
     * The ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket")
     *
     * @var Ticket
     */
    protected $ticket;

    protected $templateFile = 'emails_user:account_disabled.html.twig';

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }
}

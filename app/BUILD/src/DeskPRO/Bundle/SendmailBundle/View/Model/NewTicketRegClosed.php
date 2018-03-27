<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class NewTicketRegClosed extends EmailBaseType
{
    /**
     * An empty ticket with only the subject.
     *
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $ticket;

    protected $templateFile = 'emails_user:new_ticket_reg_closed.html.twig';

    public function __construct($subject)
    {
        $this->ticket = ['subject' => $subject];
    }
}

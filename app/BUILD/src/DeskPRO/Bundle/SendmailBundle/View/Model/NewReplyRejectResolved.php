<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class NewReplyRejectResolved extends TicketEmailType
{
    protected $templateFile = 'emails_user:new_reply_reject_resolved.html.twig';

    /**
     * Email To.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $emailTo;


    public function __construct(
        $ticket,
        $ticketPerson,
        $ticketAgent,
        $ticketLink,
        $ticketMessages,
        $ticketSatisfaction,
        $emailTo
    ) {
        parent::__construct($ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $ticketSatisfaction);

        $this->emailTo = $emailTo;
    }
}

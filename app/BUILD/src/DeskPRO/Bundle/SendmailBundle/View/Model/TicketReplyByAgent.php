<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use Application\DeskPRO\Entity\TicketFeedback;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketMessage;
use JMS\Serializer\Annotation as JMS;

class TicketReplyByAgent extends TicketEmailType
{
    /**
     * Reply written by the agent.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketMessage")
     *
     * @var TicketMessage
     */
    protected $reply;

    protected $templateFile = 'emails_user:ticket_reply_by_agent.html.twig';

    protected $showRatingLink = false;

    /**
     * TicketReplyByAgent constructor.
     *
     * @param Ticket           $ticket
     * @param Person           $ticketPerson
     * @param Person           $ticketAgent
     * @param string           $ticketLink
     * @param TicketMessage[]  $ticketMessages
     * @param TicketFeedback[] $ticketSatisfaction
     * @param TicketMessage    $reply
     * @param bool             $showRatingLink
     */
    public function __construct(
        Ticket $ticket,
        $ticketPerson,
        $ticketAgent,
        $ticketLink,
        $ticketMessages,
        $ticketSatisfaction,
        TicketMessage $reply,
        $showRatingLink
    ) {
        parent::__construct($ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $ticketSatisfaction);

        $this->reply          = $reply;
        $this->showRatingLink = $showRatingLink;
    }
}

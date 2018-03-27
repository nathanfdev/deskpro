<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class AgentTicketForward extends AgentTicketEmailType
{
    /**
     * The agent message.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $agentMessage;

    /**
     * The message subject.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $subject;

    protected $templateFile = 'emails_agent:ticket_fwd.html.twig';

    public function __construct(
        $ticket,
        $ticketPerson,
        $ticketAgent,
        $ticketLink,
        $ticketMessages,
        $participants,
        $ticketLayout,
        $customFields,
        $customUserFields,
        $agentMessage,
        $subject
    ) {
        parent::__construct($ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $participants, $ticketLayout, $customFields, $customUserFields);

        $this->agentMessage = $agentMessage;
        $this->subject      = $subject;
    }
}

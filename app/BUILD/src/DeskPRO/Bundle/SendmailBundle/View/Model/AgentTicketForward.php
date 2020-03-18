<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Blob;
use JMS\Serializer\Annotation as JMS;

class AgentTicketForward extends AgentTicketEmailType
{
    use EventCodeEmailBaseType;

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

    /**
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Blob>")
     *
     * @var Blob[]
     */
    protected $attachments;

    protected $templateFile = 'emails_agent:ticket_fwd.html.twig';

    public function __construct(
        $ticket,
        $ticketPerson,
        $ticketAgent,
        $ticketLink,
        $ticketMessages,
        $ticketSatisfaction,
        $participants,
        $ticketLayout,
        $customFields,
        $customUserFields,
        $agentMessage,
        $subject,
        $attachments
    ) {
        parent::__construct($ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $ticketSatisfaction, $participants, $ticketLayout, $customFields, $customUserFields);

        $this->agentMessage = $agentMessage;
        $this->subject      = $subject;
        $this->attachments  = $attachments;
    }
}

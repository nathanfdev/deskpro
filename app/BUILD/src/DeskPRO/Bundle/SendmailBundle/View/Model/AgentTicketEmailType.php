<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketFeedback;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketMessage;
use JMS\Serializer\Annotation as JMS;

class AgentTicketEmailType extends TicketEmailType
{
    /**
     * Participants of the ticket.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person>")
     *
     * @var array
     */
    protected $participants;

    /**
     * Information about ticket layout.
     *
     * @var array
     */
    protected $ticketLayout;

    protected $customFields;

    protected $customUserFields;

    private $eventCodeType;

    /**
     * TicketEmailType constructor.
     *
     * @param Ticket           $ticket
     * @param Person           $ticketPerson
     * @param Person           $ticketAgent
     * @param string           $ticketLink
     * @param TicketMessage[]  $ticketMessages
     * @param TicketFeedback[] $ticketSatisfaction
     * @param array            $participants
     * @param                  $ticketLayout
     * @param $customFields
     * @param $customUserFields
     */
    public function __construct(
        Ticket $ticket,
        $ticketPerson,
        $ticketAgent,
        $ticketLink,
        $ticketMessages,
        $ticketSatisfaction,
        $participants,
        $ticketLayout,
        $customFields,
        $customUserFields
    ) {
        parent::__construct($ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $ticketSatisfaction);

        $this->participants     = $participants;
        $this->ticketLayout     = $ticketLayout;
        $this->customFields     = $customFields;
        $this->customUserFields = $customUserFields;
    }

    /**
     * @return string
     */
    public function getEventCodeType()
    {
        return $this->eventCodeType;
    }

    /**
     * @param TicketEntity $ticket
     * @param PersonEntity $agent
     * @param ExecutorContextInterface $context
     */
    public function setEventCodeType(TicketEntity $ticket, $agent, ExecutorContextInterface $context)
    {
        $mentionAgents = [];
        if ($context->getVars()->has('mention_agents')) {
            $mentionAgents = $context->getVars()->get('mention_agents');
        }

        foreach ($mentionAgents as $mentionAgent) {
            if ($mentionAgent->getId() === $agent->getId()) {
                $this->eventCodeType = 'im';

                return;
            }
        }

        $state                = $ticket->getStateChangeRecorder();
        list($before, $after) = $state->getBeforeAfterModels();
        if ($after->agent === $agent->getId() || $before->agent === $agent->getId()) {
            $this->eventCodeType = 'ticket_you';

            return;
        }
        if (in_array($agent->getId(), $after->followers) || in_array($agent->getId(), $before->followers)) {
            $this->eventCodeType = 'ticket_follow';

            return;
        }
        if (in_array($after->agent_team, $agent->getTeamIds()) || in_array($before->agent_team, $agent->getTeamIds())) {
            $this->eventCodeType = 'ticket_team';

            return;
        }
        if ($this instanceof AgentTicketNew) {
            $this->eventCodeType = 'ticket_new';

            return;
        }
        $this->eventCodeType = 'ticket';

        return;
    }
}

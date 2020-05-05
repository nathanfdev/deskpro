<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\BasePerson;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketFeedback;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketMessage;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Arrays;

abstract class TicketEmailType extends EmailBaseType
{
    /**
     * The ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket")
     *
     * @var Ticket
     */
    protected $ticket;

    /**
     * The person for whom opened the ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var BasePerson
     */
    protected $ticketPerson;

    /**
     * The agent assigned to the ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var BasePerson
     */
    protected $ticketAgent;

    /**
     * Messages sent within the ticket.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketMessage>")
     *
     * @var TicketMessage[]
     */
    protected $ticketMessages;

    /**
     * A link to the ticket.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $ticketLink;

    /**
     * Ticket satisfactions.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketFeedback>")
     *
     * @var TicketFeedback[]
     */
    protected $ticketSatisfaction;

    /**
     * The person who made the action with ticket.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $actionPerformer;

    /**
     * Context
     *
     * @JMS\Type("array")
     *
     * @var []
     */
    protected $context;

    /**
     * TicketEmailType constructor.
     *
     * @param Ticket           $ticket
     * @param BasePerson       $ticketPerson
     * @param BasePerson       $ticketAgent
     * @param string           $ticketLink
     * @param TicketMessage[]  $ticketMessages
     * @param TicketFeedback[] $ticketSatisfaction
     */
    public function __construct(Ticket $ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $ticketSatisfaction)
    {
        $this->ticket = $ticket;

        $this->ticketPerson = $ticketPerson;

        $this->ticketLink = $ticketLink;

        $this->ticketAgent = $ticketAgent;

        $this->ticketMessages = $ticketMessages;

        $this->ticketSatisfaction = $ticketSatisfaction;
    }

    /**
     * @param BasePerson $performer
     *
     * @return $this
     */
    public function setActionPerformer(BasePerson $performer)
    {
        $this->actionPerformer = $performer;

        return $this;
    }

    /**
     * @param TicketEntity $ticket
     * @param ExecutorContextInterface $context
     * @param $mode
     */
    public function setContextVars(TicketEntity $ticket, $context, $mode)
    {
        $this->context = $this->getStandardEmailVars($ticket, $context, $mode);
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param TicketEntity $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $mode    'user' or 'agent'
     *
     * @return array
     */
    protected function getStandardEmailVars(TicketEntity $ticket, ExecutorContextInterface $context, $mode)
    {
        //------------------------------
        // Build up some type flags
        //------------------------------

        $state = $ticket->getStateChangeRecorder();
        if ($state->isNewTicket()) {
            $type = 'newticket';
        } elseif ($state->hasChangedField('message')) {
            $type = 'newreply';
        } else {
            $type = 'updated';
        }

        $context->getLogger()->info("[AbstractEmailAction] Type: $type");
        $context->getLogger()->info(sprintf('[AbstractEmailAction] Performer: %s', $context->getEventPerformer()));

        //------------------------------
        // Set reply flags
        //------------------------------

        $newReplies      = $state->getNewReplies();
        $isNewTicket     = $state->isNewTicket();
        $isNewAgentReply = false;
        $isNewAgentNote  = false;
        $isNewUserReply  = false;

        foreach ($newReplies as $message) {
            if ($message->is_agent_note) {
                $isNewAgentNote = true;
            } elseif ($message->person->is_agent) {
                $isNewAgentReply = true;
            } else {
                $isNewUserReply = true;
            }
        }

        // In user mode, never show notes
        if ($mode == 'user') {
            $newReplies = array_filter($newReplies, function ($r) {
                return !$r->is_agent_note;
            });
            $ticketLogs = null;

        // Agent mode - include ticket logs
        } else {
            $ticketLogGenerator = new TicketLogGenerator($ticket, $context);
            $ticketLogs         = $ticketLogGenerator->getLogEntries();
        }

        //------------------------------
        // Build map of mentions
        //------------------------------

        $vars = [
            'type'               => $type,
            'user_mode'          => $mode,
            'performer_type'     => $context->getEventPerformer(),
            'is_new_ticket'      => $isNewTicket,
            'is_new_agent_reply' => $isNewAgentReply,
            'is_new_agent_note'  => $isNewAgentNote,
            'is_new_user_reply'  => $isNewUserReply,
            'is_status_change'   => $state->hasChangedField('status'),
            'action_performer'   => $context->getPersonContext(),
            'new_message'        => Arrays::getFirstItem($newReplies),
            'new_messages'       => $newReplies,
            'ticket_logs'        => $ticketLogs,
            'user_vars'          => $context->getUserVars(),
        ];

        return $vars;
    }
}

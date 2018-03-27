<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonContextInterface;

/**
 * Sets flag.
 */
class FlagAction extends AbstractAction implements PersonContextInterface, ExecutionContextAware
{
    /** @var string */
    protected $flag;
    /** @var Person */
    protected $person_context;
    /** @var string|null */
    protected $execution_context = null;

    public function __construct($flag)
    {
        $this->flag = $flag;
    }

    /**
     * {@inheritdoc}
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * {@inheritdoc}
     */
    public function setExecutionContext($context)
    {
        $this->execution_context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $flag = $this->flag;

        // Context of a trigger, flagging means flag for everyone
        if ($this->execution_context == 'trigger') {
            $agents = App::getEntityRepository('DeskPRO:Person')->getAgents();
            foreach ($agents as $a) {
                $ticket->setFlagForPerson($a, $flag);
            }

        // Otherwise its a macro, flag for the performer
        } else {
            // Invalid context
            if (!$this->person_context or !$this->person_context['is_agent']) {
                return;
            }

            $ticket->setFlagForPerson($this->person_context, $flag);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            ['action' => 'flag', 'color' => $this->flag],
        ];
    }

    /**
     * Get the flag color.
     *
     * @return int
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();

        if (!$this->flag) {
            return $tr->phrase('agent.tickets.unset_flag_action');
        } else {
            return $tr->phrase('agent.tickets.set_flag_to_action', ['flag' => $this->flag]);
        }
    }
}

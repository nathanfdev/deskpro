<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\SnippetFormatter;
use Orb\Util\CheckedOptionsArray;

/**
 * Adds a reply to the ticket.
 *
 * @option string note_text
 * @option int    by_agent_id
 * @option bool   by_assigned_agent
 * @option bool   no_formatter
 */
class AddAgentNote extends AbstractContainerAwareAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('by_agent_id');
        $options->addRequiredNames('note_text');
        $options->addValidNames('by_assigned_agent');
        $options->addValidNames('no_formatter');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $agent = null;
        if ($this->getActionOption('by_assigned_agent') && $ticket->agent) {
            $agent = $ticket->agent;
        }
        if (!$agent) {
            $agent = $this->getContainer()->getAgentData()->get($this->getActionOption('by_agent_id'));
        }

        if (!$agent) {
            return;
        }

        $em = $this->getContainer()->getEm();
        $context->setPersonContext($agent);
        $message                  = new TicketMessage();
        $message->person          = $agent;
        $message->date_created    = new \DateTime('+1 second');
        $message['is_agent_note'] = true;

        $note_text = $this->getActionOption('note_text');

        if (!$this->getActionOption('no_formatter')) {
            $formatter = new SnippetFormatter($this->getContainer()->getTwig());
            ActionVars::configureFormatter($formatter, $context);

            $note_text = $formatter->formatText($note_text, $ticket);
        }

        $message->setMessage($note_text);

        $ticket->addMessage($message);
        $em->persist($message);
    }
}

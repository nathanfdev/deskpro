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
 * @option string reply_text
 * @option int    by_agent_id
 * @option bool   by_assigned_agent
 * @option bool   no_formatter
 */
class AddAgentReply extends AbstractContainerAwareAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('by_agent_id');
        $options->addRequiredNames('reply_text');
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

        $message               = new TicketMessage();
        $message->person       = $agent;
        $message->date_created = new \DateTime('+1 second');

        $reply_text = $this->getActionOption('reply_text');

        if (!$this->getActionOption('no_formatter')) {
            $formatter = new SnippetFormatter($this->getContainer()->getTwig());
            ActionVars::configureFormatter($formatter, $context);

            $reply_text = $formatter->formatTemplate($reply_text, $ticket, $context);
        }

        $reply_text = $this->getContainer()->getInputCleaner()->clean($reply_text, 'html');
        $reply_text = \Orb\Util\Strings::trimHtml($reply_text);
        $reply_text = \Orb\Util\Strings::prepareWysiwygHtml($reply_text);

        if (!$reply_text) {
            $context->getLogger()->notice('[AddAgentReply] Reply text evaluates to an empty string');

            return;
        }

        $message->setMessage($reply_text);

        $ticket->addMessage($message);
        $em->persist($message);
    }
}

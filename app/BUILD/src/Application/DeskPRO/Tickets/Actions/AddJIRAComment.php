<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Service\JIRA;
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
class AddJIRAComment extends AbstractContainerAwareAction implements ActionInterface, NoopableInterface
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

        $note_text = $this->getActionOption('note_text');

        if (!$this->getActionOption('no_formatter')) {
            $formatter = new SnippetFormatter($this->getContainer()->getTwig());
            $formatter->addVar('user_vars', $context->getUserVars());
            $note_text = $formatter->formatText($note_text, $ticket);
        }

        /** @var JIRA $js */
        $js = $this->getContainer()->get(JIRA::NAME);
        foreach ($ticket->jira_issues as $issue) {
            try {
                $js->createComment($issue['issue_id'], $agent, $ticket, $note_text);
            } catch (\Exception $e) {
                $context->getLogger()->error(
                    sprintf('[JIRAAddComment] Exception: [%s] %s', $e->getCode(), $e->getMessage()),
                    ['exception' => $e]
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        /** @var JIRA $js */
        $js = $this->getContainer()->get(JIRA::NAME);

        return !$js->isEnabled();
    }
}

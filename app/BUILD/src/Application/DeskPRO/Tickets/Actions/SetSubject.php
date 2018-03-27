<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\SnippetFormatter;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the subject.
 *
 * @option string subject
 */
class SetSubject extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('subject');
        $options->addValidNames('with_formatter');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getActionOption('subject')) {
            return;
        }

        $subject = $this->getActionOption('subject');

        if ($this->getActionOption('with_formatter')) {
            $formatter = new SnippetFormatter($this->getContainer()->getTwig());
            ActionVars::configureFormatter($formatter, $context);

            $subject = $formatter->formatText('{% autoescape false %}'.$subject.'{% endautoescape %}', $ticket);
            $subject = preg_replace("#[\r\n]#", ' ', $subject);
            $subject = preg_replace('#\\s{2,}#', ' ', $subject);
            $subject = trim($subject);

            if (!$subject) {
                $context->getLogger()->notice('[SetSubject] Subject pattern evaluates to an empty string');

                return;
            }
        }

        if ($subject !== $ticket->getSubject()) {
            $ticket->setSubject($subject);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($ticket->subject == $this->getActionOption('subject') || !$this->getActionOption('subject')) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
            return ['fields'];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}

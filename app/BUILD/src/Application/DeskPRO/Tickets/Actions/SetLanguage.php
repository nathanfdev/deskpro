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
use Orb\Util\CheckedOptionsArray;

/**
 * Set the language.
 *
 * @option int language_id
 */
class SetLanguage extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('language_id');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_lang_id = $this->getActionOption('language_id');

        if ($set_lang_id) {
            $lang = $this->getContainer()->getLanguageData()->get($set_lang_id);
            if (!$lang) {
                return; //invalid
            }
        } else {
            $lang = null;
        }

        $ticket->language = $lang;
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_lang_id    = $this->getActionOption('language_id');
        $ticket_lang_id = $ticket->language ? $ticket->language->id : 0;

        if ($ticket_lang_id == $set_lang_id) {
            return true;
        }

        if ($set_lang_id) {
            $lang = $this->getContainer()->getLanguageData()->get($set_lang_id);
            if (!$lang) {
                return true; //invalid
            }
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

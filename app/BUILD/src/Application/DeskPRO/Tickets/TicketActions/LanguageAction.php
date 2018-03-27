<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class LanguageAction extends AbstractAction implements PermissionableAction
{
    /** @var int */
    protected $language_id;

    public function __construct($language)
    {
        $this->language_id = $language;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $ticket['language_id'] = $this->language_id;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if ($ticket->getLanguageId() == $this->language_id) {
            return true;
        }

        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        if ($ticket['language_id'] == $this->language_id) {
            return [];
        }

        return [
            ['action' => 'language', 'language_id' => $this->language_id],
        ];
    }

    /**
     * Get the language id.
     *
     * @return int
     */
    public function getLanguageId()
    {
        return $this->language_id;
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

        $names = App::getDataService('Language')->getTitles();
        if (!isset($names[$this->language_id])) {
            $name = "<error>Unknown #{$this->language_id}</error>";
        } else {
            $name = $as_html ? htmlspecialchars($names[$this->language_id]) : $names[$this->language_id];
        }

        return $tr->phrase('agent.tickets.set_language_action', ['language' => $name]);
    }
}

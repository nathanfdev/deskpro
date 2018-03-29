<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class CategoryAction extends AbstractAction implements PermissionableAction
{
    /** @var int */
    protected $category_id;

    public function __construct($category)
    {
        $this->category_id = $category;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $ticket['category_id'] = $this->category_id;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if ($ticket->getCategoryId() == $this->category_id) {
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
        if ($ticket['category_id'] == $this->category_id) {
            return [];
        }

        return [
            ['action' => 'category', 'category_id' => $this->category_id],
        ];
    }

    /**
     * Get the category id.
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->category_id;
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

        if ($this->category_id == 0) {
            return $tr->phrase('agent.tickets.remove_category_action');
        } else {
            $names = App::getEntityRepository('DeskPRO:TicketCategory')->getFullNames();
            if (!isset($names[$this->category_id])) {
                $name = "<error>Unknown #{$this->category_id}</error>";
            } else {
                $name = $names[$this->category_id];
            }

            return $tr->phrase('agent.tickets.set_category_action', ['category' => $name]);
        }
    }
}

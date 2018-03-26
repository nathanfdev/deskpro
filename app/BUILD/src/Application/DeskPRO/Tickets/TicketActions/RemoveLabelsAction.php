<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Orb\Util\Arrays;

/**
 * Removes labels.
 */
class RemoveLabelsAction extends AbstractAction implements PermissionableAction
{
    /** @var array */
    protected $remove_labels;

    public static function newFromString($remove_labels)
    {
        $remove_labels = explode(',', $remove_labels);

        return new self($remove_labels);
    }

    public function __construct(array $remove_labels)
    {
        array_walk($remove_labels, 'trim');
        $remove_labels = Arrays::removeEmptyString($remove_labels);

        $this->remove_labels = $remove_labels;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        if (!$this->remove_labels) {
            return;
        }

        $ticket->getLabelManager()->removeLabels($this->remove_labels);
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'labels')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        $removed_labels = [];
        foreach ($this->remove_labels as $l) {
            if (!$ticket->getLabelManager()->hasLabel($l)) {
                $removed_labels[] = $l;
            }
        }

        if (!$removed_labels) {
            return [];
        }

        return [
            ['action' => 'remove_labels', 'label' => $removed_labels],
        ];
    }

    /**
     * Get labels.
     *
     * @return array
     */
    public function getLabels()
    {
        return $this->remove_labels;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        $labels = array_merge($this->remove_labels, $otherAction->getLabels());
        $labels = array_unique($labels);

        return new self($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();

        return $tr->phrase('agent.tickets.remove_labels_action', ['labels' => implode(', ', $this->remove_labels)]);
    }
}

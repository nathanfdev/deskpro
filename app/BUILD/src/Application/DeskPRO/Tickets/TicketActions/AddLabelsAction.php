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
 * Adds labels.
 */
class AddLabelsAction extends AbstractAction implements PermissionableAction
{
    /** @var array */
    protected $add_labels;

    public static function newFromString($add_labels)
    {
        $add_labels = explode(',', $add_labels);

        return new self($add_labels);
    }

    public function __construct(array $add_labels)
    {
        array_walk($add_labels, 'trim');
        $add_labels = Arrays::removeEmptyString($add_labels);

        $this->add_labels = $add_labels;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        if (!$this->add_labels) {
            return;
        }

        $ticket->getLabelManager()->addLabels($this->add_labels);
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
        $added_labels = [];
        foreach ($this->add_labels as $l) {
            if (!$ticket->getLabelManager()->hasLabel($l)) {
                $added_labels[] = $l;
            }
        }

        if (!$added_labels) {
            return [];
        }

        return [
            ['action' => 'add_labels', 'label' => $added_labels],
        ];
    }

    /**
     * Get labels.
     *
     * @return array
     */
    public function getLabels()
    {
        return $this->add_labels;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        $labels = array_merge($this->add_labels, $otherAction->getLabels());
        $labels = array_unique($labels);

        return new self($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();

        return $tr->phrase('agent.tickets.add_labels_action', ['labels' => implode(', ', $this->add_labels)]);
    }
}

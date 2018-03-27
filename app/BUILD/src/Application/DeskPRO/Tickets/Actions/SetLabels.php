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
use Application\DeskPRO\Util as DeskPROUtil;
use Orb\Util\CheckedOptionsArray;

/**
 * Adds and removes lables from tickets.
 *
 * @option string[] remove_labels  Array of labels to remove from the ticket
 * @option string[] add_labels     Array of labels to add to the ticket
 */
class SetLabels extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('remove_labels', 'add_labels');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        //--------------------
        // Add labels
        //--------------------

        $add_labels = DeskPROUtil::labelsArrayFromString($this->getActionOption('add_labels', ''), false);

        if ($add_labels) {
            foreach ($add_labels as $l) {
                $ticket->addLabelByString($l);
            }
        }

        //--------------------
        // Remove labels
        //--------------------

        $remove_labels = DeskPROUtil::labelsArrayFromString($this->getActionOption('remove_labels', ''));

        if ($remove_labels) {
            foreach ($remove_labels as $l) {
                $ticket->removeLabelByString($l);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'labels')) {
            return ['labels'];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}

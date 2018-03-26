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
 * Set the category.
 *
 * @option int category_id
 */
class SetCategory extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('category_id');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_cat_id = $this->getActionOption('category_id');

        if ($set_cat_id) {
            $cat = $this->getContainer()->getTicketCategories()->getSettableById($set_cat_id);
            if (!$cat) {
                return; //invalid
            }
        } else {
            $cat = null;
        }

        $ticket->category = $cat;
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_cat_id    = $this->getActionOption('category_id');
        $ticket_cat_id = $ticket->category ? $ticket->category->id : 0;

        if ($ticket_cat_id == $set_cat_id) {
            return true;
        }

        if ($set_cat_id) {
            $cat = $this->getContainer()->getTicketCategories()->getSettableById($set_cat_id);
            if (!$cat) {
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

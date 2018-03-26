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
 * Set the product.
 *
 * @option int product_id
 */
class SetProduct extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('product_id');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_prod_id = $this->getActionOption('product_id');

        if ($set_prod_id) {
            $prod = $this->getContainer()->getProducts()->getSettableById($set_prod_id);
            if (!$prod) {
                return;
            }
        } else {
            $prod = null;
        }

        $ticket->product = $prod;
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_prod_id    = $this->getActionOption('product_id');
        $ticket_prod_id = $ticket->product ? $ticket->product->id : 0;

        if ($ticket_prod_id == $set_prod_id) {
            return true;
        }

        if ($set_prod_id) {
            $prod = $this->getContainer()->getProducts()->getSettableById($set_prod_id);
            if (!$prod) {
                return true;
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

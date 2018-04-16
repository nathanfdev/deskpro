<?php

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Class AddBrandToPerson.
 */
class AddBrandToPerson extends AbstractContainerAwareAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $person = $ticket->getPerson();
        $brand  = $ticket->getBrand();

        // nothing to set, skipping
        if (!$brand) {
            return;
        }

        // no person, skipping
        if (!$person) {
            return;
        }

        if (!$person->hasBrand($brand)) {
            $person->addBrand($brand);
        }
    }
}

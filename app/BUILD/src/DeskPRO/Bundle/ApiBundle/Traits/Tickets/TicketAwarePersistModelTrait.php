<?php

namespace DeskPRO\Bundle\ApiBundle\Traits\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Symfony\Component\Form\FormInterface;

/**
 * Class TicketAwarePersistModelTrait.
 *
 * @method saveTicket(Ticket $ticket)
 */
trait TicketAwarePersistModelTrait
{
    /**
     * {@inheritdoc}
     */
    protected function persistModel($entity, FormInterface $form = null)
    {
        $this->saveTicket($entity->getTicket(), ['ticketAwareEntity' => $entity]);

        return $entity;
    }
}

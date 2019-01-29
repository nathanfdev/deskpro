<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use Symfony\Component\Form\FormInterface;

/**
 * Class AbstractTicketsController.
 */
abstract class AbstractTicketsController extends CrudController
{
    use TicketSaveTrait;

    public static $entity = Ticket::class;

    /**
     * {@inheritdoc}
     *
     * @param Ticket $entity
     */
    protected function persistModel($entity, FormInterface $form = null)
    {
        $options = [];
        if ($form->has('suppress_user_notify')) {
            $options['suppress_user_notify'] = $form->get('suppress_user_notify')->getData();
        }
        if ($form->has('context')) {
            $options['context'] = $form->get('context')->getData();
        }

        $this->saveTicket($entity, $options);

        return $entity;
    }
}

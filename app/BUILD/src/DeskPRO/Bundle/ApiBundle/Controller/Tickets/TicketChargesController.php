<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\TicketCharge;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketAwarePersistModelTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketChargeType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketChargesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tickets/{parentId}/charges")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\TicketCharge")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketChargeType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\TicketCharge",
 *          "ticket"="Application\DeskPRO\Entity\Ticket",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class TicketChargesController extends CrudSubController
{
    use TicketSaveTrait, TicketAwarePersistModelTrait;

    public static $entity         = TicketCharge::class;
    public static $type           = TicketChargeType::class;
    public static $parentProperty = 'ticket';

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'ticket' => $this->findParentOr404(),
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function persistModel($entity, FormInterface $form = null)
    {
        $entity->getTicket()->disableAutoTicketProcess();

        if (!$entity->getId()) {
            // save new TicketCharge through Ticket save process and generate log entries
            $this->saveTicket($entity->getTicket());
        } else {
            $entity->getTicket()->getStateChangeRecorder()->record('charge_changed', $entity, $entity);
            $this->saveTicket($entity->getTicket());
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteEntity($entity)
    {
        // need this call to properly generate log data
        $entity->resetCustomData();

        $ticket = $entity->getTicket();
        $ticket->disableAutoTicketProcess();
        $ticket->removeCharge($entity);

        $this->saveTicket($ticket);
    }
}

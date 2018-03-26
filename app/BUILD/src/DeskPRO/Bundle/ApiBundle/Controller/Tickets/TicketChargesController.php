<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\TicketCharge;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketChargeType;
use FOS\RestBundle\Controller\Annotations as Rest;
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
}

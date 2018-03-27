<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\MassActions;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\Tickets\TicketMassActionsType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class TicketMassActionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/mass_actions/tickets")
 * @ApiDoc(target="all", section="Mass actions")
 * @ApiDoc(
 *     target="massAction",
 *     input="DeskPRO\Bundle\AppBundle\Form\Type\MassActions\Tickets\TicketMassActionsType"
 * )
 */
class TicketMassActionsController extends AbstractMassActionsController
{
    use TicketSaveTrait;

    protected static $type = TicketMassActionsType::class;

    /**
     * {@inheritdoc}
     */
    protected function saveObject($entity)
    {
        $this->saveTicket($entity);
    }
}

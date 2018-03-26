<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\Filters;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @ApiModes("all")
 * @Rest\Route("/ticket_filters2_sets")
 * @Feature("new_filters")
 * @ApiDoc(
 *     target="all",
 *     section="Ticket filters (new)",
 *     output="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet"
 * )
 */
class TicketFilterSetsController extends CrudController
{
    public static $exposeOnly = ['list', 'get'];
    public static $entity     = TicketFilterSet::class;
    public static $listSort   = 'displayOrder';
    public static $listOrder  = 'asc';
}

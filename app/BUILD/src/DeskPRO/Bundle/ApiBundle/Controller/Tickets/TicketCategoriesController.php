<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\TicketCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class TicketCategoriesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_categories")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\TicketCategory")
 */
class TicketCategoriesController extends CrudController
{
    public static $exposeOnly   = ['get', 'list', 'count'];
    public static $entity       = TicketCategory::class;
    public static $listPaginate = false;
}

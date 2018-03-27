<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\TicketWorkflow;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class TicketWorkflowController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_workflows")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\TicketWorkflow")
 */
class TicketWorkflowsController extends CrudController
{
    public static $exposeOnly   = ['get', 'list', 'count'];
    public static $entity       = TicketWorkflow::class;
    public static $listPaginate = false;
}

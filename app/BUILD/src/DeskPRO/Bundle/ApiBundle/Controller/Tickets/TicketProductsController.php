<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Product;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class TicketProductController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_products")
 * @Rest\View(serializerGroups={"list", "details", "product"})
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\Product")
 */
class TicketProductsController extends CrudController
{
    public static $exposeOnly   = ['get', 'list', 'count'];
    public static $entity       = Product::class;
    public static $listPaginate = false;
}

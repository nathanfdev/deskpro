<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Product;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\SearchHelper;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $context = new RequestQueryContext($qb, $alias, $request);
        SearchHelper::applyFieldFilter($context, 'search', 'title');
    }
}

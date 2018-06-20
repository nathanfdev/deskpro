<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\TicketPriority;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketPrioritiesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_priorities")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\TicketPriority")
 */
class TicketPrioritiesController extends CrudController
{
    public static $exposeOnly   = ['get', 'list', 'count'];
    public static $entity       = TicketPriority::class;
    public static $listPaginate = false;

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        if ($request->get('title')) {
            $qb->andWhere("$alias.title LIKE :title");
            $qb->setParameter('title', '%'.addcslashes($request->get('title'), '%_').'%');
        }
    }
}

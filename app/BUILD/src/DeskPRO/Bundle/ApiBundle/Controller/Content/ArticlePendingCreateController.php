<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\ArticlePendingCreate;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ArticlePendingCreateController.
 *
 * @ApiModes("all")
 * @Rest\Route("/article_pending_creates")
 * @ApiDoc(target="all", section="Content", output="ArticlePendingCreate")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="assigned_person", "dataType"="string|integer", "pattern"="me|\d+"}
 *     }
 * )
 * @ApiDoc(
 *     target="listAction",
 *     filters={
 *         {"name"="order_by", "pattern"="date_created|assigned_person", "description"="how to order result", "dataType"="string"},
 *         {"name"="order_dir", "pattern"="asc|desc", "description"="order direction", "dataType"="string"}
 *     }
 * )
 */
class ArticlePendingCreateController extends CrudController
{
    public static $exposeOnly  = ['get', 'list', 'count', 'delete'];
    public static $entity      = ArticlePendingCreate::class;
    public static $sortOptions = [
        'date_created'    => 'date_created',
        'assigned_person' => 'assigned_person',
    ];

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $assignedPerson = $request->get('assigned_person');
        if ($assignedPerson) {
            if ($assignedPerson === 'me') {
                $assignedPerson = $this->getUser()->getId();
            }

            $qb->andWhere("$alias.assigned_person IN (:assigned_person)");
            $qb->setParameter('assigned_person', $assignedPerson);
        }
    }
}

<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\AgentAlerts;

use Application\DeskPRO\Entity\AgentAlert;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AgentAlertsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/me/notifications")
 * @ApiDoc(target="all", section="Notifications and alerts", output="DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts\AgentAlert")
 */
class AgentAlertsController extends CrudController
{
    public static $entity     = AgentAlert::class;
    public static $listSort   = 'date_created';
    public static $listOrder  = 'desc';
    public static $exposeOnly = ['get', 'list', 'count', 'delete'];

    /**
     * Dismiss alerts with given ids array.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about agent alerts",
     *     description="Dismiss set of alerts",
     *     statusCodes={
     *         204="Returned if everything is ok",
     *     },
     *     parameters={
     *         {"name"="alert_ids", "description"="", "dataType"="array", "required"=true}
     *     },
     *     noOutput=true
     * )
     * @Rest\Post("/dismiss")
     *
     * @param Request $request
     *
     * @return View
     */
    public function dismissAction(Request $request)
    {
        $ids = $request->get('alert_ids');
        if ($ids) {
            $qb = $this->getManager()->createQueryBuilder();
            $qb
                ->update(AgentAlert::class, 'a')
                ->set('a.is_dismissed', 1)
                ->where('a.id IN (:ids)')
                ->setParameter('ids', $ids)
            ;

            $this->applyUserFilter($qb, 'a');
            $qb->getQuery()->execute();
        }

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * You can dismiss all users alerts for current authenticated user.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about agent alerts",
     *     description="dismiss all alerts for the current user",
     *     statusCodes={
     *         200="Returned if everything is ok",
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     * @Rest\Post("/dismiss/all")
     *
     * @return View
     */
    public function dismissAllAction()
    {
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->update(AgentAlert::class, 'a')
            ->set('a.is_dismissed', 1)
            ->where('a.is_dismissed = 0')
        ;

        $this->applyUserFilter($qb, 'a');
        $qb->getQuery()->execute();

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $this->applyUserFilter($qb, $alias);

        if (null !== $request->get('after')) {
            $qb->andWhere("$alias.date_created > :date");
            $qb->setParameter('date', $request->get('after'));
        }
        if (null !== $request->get('is_dismissed')) {
            $qb->andWhere("$alias.is_dismissed = :is_dismissed");
            $qb->setParameter('is_dismissed', $request->get('is_dismissed'));
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     */
    protected function applyUserFilter(QueryBuilder $qb, $alias)
    {
        $qb->andWhere("$alias.person = :user");
        $qb->setParameter('user', $this->getUser());
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        if ($groupBy === 'is_dismissed') {
            $qb
                ->addSelect("(CASE WHEN $alias.is_dismissed = 1 THEN 'dismissed' ELSE 'non_dismissed' END) as group_name")
                ->addSelect("(CASE WHEN $alias.is_dismissed = 1 THEN 'Dismissed' ELSE 'Non dismissed' END) as title")
                ->groupBy('group_name')
            ;
        }
    }
}

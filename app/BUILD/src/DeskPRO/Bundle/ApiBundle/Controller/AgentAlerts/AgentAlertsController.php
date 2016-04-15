<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\AgentAlerts;

use Application\DeskPRO\Entity\AgentAlert;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AgentAlertsController.
 *
 * @ApiDocSection("Notifications and alerts")
 * @OutputEntity("DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts\AgentAlert")
 * @ApiModes("all")
 * @Rest\Route("/me/notifications")
 */
class AgentAlertsController extends CrudController
{
    public static $entity    = AgentAlert::class;
    public static $listSort  = 'date_created';
    public static $listOrder = 'desc';

    /**
     * Get user's notification counts.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about agent alerts",
     *     description="Get notifications counts",
     *     statusCodes={
     *         204="Returned if everything is ok",
     *     }
     * )
     *
     * @Rest\Get("/counts")
     *
     * @return View
     */
    public function getCountsAction()
    {
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('count(a.id) as group_count, a.is_dismissed')
            ->from(AgentAlert::class, 'a')
            ->where('a.person = :user')
            ->setParameter('user', $this->getUser())
            ->groupBy('a.is_dismissed')
        ;

        $count  = Count::fromGroupedBy('is_dismissed');
        $result = $qb->getQuery()->getResult();
        if (is_array($result)) {
            foreach ($result as $item) {
                $type = $item['is_dismissed'] ? 'dismissed' : 'non_dismissed';
                $count->addNested($item['group_count'], null, $type, null, true);
            }
        }

        return new View($this->wrap($count));
    }

    /**
     * Dismiss alerts with given ids array.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about agent alerts",
     *     description="Dismiss set of alerts",
     *     statusCodes={
     *         204="Returned if everything is ok",
     *     }
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
                ->getQuery()
                ->execute()
            ;
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
     *     }
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
            ->where(
                'a.is_dismissed = 0',
                'a.person = :user'
            )
            ->setParameter('user', $this->getUser())
            ->getQuery()
            ->execute()
        ;

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        if (null !== $request->get('after')) {
            $qb->andWhere("$alias.date_created > :date");
            $qb->setParameter('date', $request->get('after'));
        }
        if (null !== $request->get('is_dismissed')) {
            $qb->andWhere("$alias.is_dismissed = :is_dismissed");
            $qb->setParameter('is_dismissed', $request->get('is_dismissed'));
        }
    }
}

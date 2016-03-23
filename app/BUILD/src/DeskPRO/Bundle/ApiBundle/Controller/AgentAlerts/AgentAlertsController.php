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
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AgentAlertsController.
 *
 * @ApiDocSection("Notifications and alerts")
 * @ApiModes("all")
 * @Annotations\Route("/me/notifications")
 */
class AgentAlertsController extends CrudController
{
    public static $entity        = AgentAlert::class;
    public static $output_entity = AgentAlert::class;
    public static $listSort      = 'date_created';
    public static $listOrder     = 'desc';

    /**
     * Dismiss alerts with given ids array.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about agent alerts",
     *     description="Dismiss set of alerts",
     *     statusCodes={
     *         200="Returned if everything is ok",
     *     }
     * )
     * @Annotations\Post("/dismiss")
     *
     * @param Request $request
     *
     * @return View
     */
    public function dismissAction(Request $request)
    {
        $em  = $this->getManager();
        $ids = $request->get('alert_ids');
        if ($ids) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('alert')
                ->from('DeskPRO:AgentAlert', 'alert')
                ->where('alert.id IN (:ids)')
                ->setParameter('ids', $ids);
            $alerts = $qb->getQuery()->getResult();
            foreach ($alerts as $alert) {
                $alert->is_dismissed = true;
            }
            $em->flush();
        }

        return View::create([], Response::HTTP_OK);
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
     * @Annotations\Post("/dismiss/all")
     *
     * @return View
     */
    public function dismissAllAction()
    {
        $em = $this->getManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('alert')
            ->from('DeskPRO:AgentAlert', 'alert')
            ->where('alert.is_dismissed = :false')
            ->setParameter('false', false)
            ->andWhere('alert.person = :user')
            ->setParameter('user', $this->getUser());
        $alerts = $qb->getQuery()->getResult();
        foreach ($alerts as $alert) {
            $alert->is_dismissed = true;
        }
        $em->flush();

        return View::create([], Response::HTTP_OK);
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

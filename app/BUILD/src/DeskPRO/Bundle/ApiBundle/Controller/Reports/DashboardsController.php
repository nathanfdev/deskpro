<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Reports;

use Application\DeskPRO\Entity\ReportDashboard;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Type\Reports\ReportDashboardType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ReportDashboardsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/dashboards")
 * @ApiDoc(target="all", section="Reports", output="Application\DeskPRO\Entity\ReportDashboard")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Reports\ReportDashboardType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ReportDashboard"
 *      }
 *     }
 * )
 * @Feature("new_reports")
 */
class DashboardsController extends CrudController
{
    public static $entity       = ReportDashboard::class;
    public static $type         = ReportDashboardType::class;
    public static $listPaginate = false;

    /**
     * @Rest\Get("/{id}/reports")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function getReportsAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, $this->getPermissionGroupEntityContext($id, $request));

        return DashboardReportsController::subRequestSearch($this->getKernel(), $request, [
            'dashboard' => $id,
        ]);
    }

    /**
     * @Rest\Post("/{dashboard}/clone", requirements={"dashboard"="\d+"})
     *
     * @param ReportDashboard $dashboard
     * @param Request         $request
     *
     * @return View
     */
    public function cloneAction(ReportDashboard $dashboard, Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, $this->getPermissionGroupEntityContext($dashboard->getId(), $request));

        $cloneDashboard = $this->get('reports.dashboard_manager')->cloneDashboard($dashboard);

        $this->getManager()->persist($cloneDashboard);
        $this->getManager()->flush();

        return new View($this->wrap($cloneDashboard), Response::HTTP_CREATED);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb
            ->leftJoin("$alias.permissions", 'p')
            ->andWhere("p.person IN (:person) OR p.team IN (:teams) OR $alias.person IN (:person)")
            ->setParameter('person', $this->getUser())
            ->setParameter('teams', $this->getUser()->getTeams())
        ;
    }
}

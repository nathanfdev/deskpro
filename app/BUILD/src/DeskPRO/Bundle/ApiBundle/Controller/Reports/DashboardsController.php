<?php

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

    public static $sortOptions = [
        'id'            => 'id',
        'display_order' => 'display_order',
        'title'         => 'title',
    ];

    public static $listSort  = 'display_order';
    public static $listOrder = 'asc';

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
     * @Rest\Get("/{id}/shareable_links")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function getShareableLinksAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, $this->getPermissionGroupEntityContext($id, $request));

        return DashboardShareableLinksController::subRequestSearch($this->getKernel(), $request, [
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
        if ($this->getUser()->isAdmin() || $this->getUser()->can_reports) {
            return;
        }

        $qb
            ->leftJoin("$alias.permissions", 'p')
            ->andWhere("
                (p.person IN (:person) OR p.team IN (:teams) OR $alias.person IN (:person))
                OR
                (p.id IS NOT NULL AND p.person IS NULL AND p.team IS NULL AND p.department IS NULL)                
            ")
            ->setParameter('person', $this->getUser())
            ->setParameter('teams', $this->getUser()->getTeams())
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function applySorting(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applySorting($qb, $alias, $request);
        $qb->addOrderBy($alias.'.title', 'asc');
    }
}

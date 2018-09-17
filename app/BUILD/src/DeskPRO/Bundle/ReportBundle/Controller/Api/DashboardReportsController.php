<?php

namespace DeskPRO\Bundle\ReportBundle\Controller\Api;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboardReport;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\ReportBundle\Form\Type\ReportDashboardReportType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ReportDashboardReportsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/dashboard_reports")
 * @ApiDoc(target="all", section="Reports", output="Application\DeskPRO\Entity\ReportDashboardReport")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\ReportBundle\Form\Type\ReportDashboardReportType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ReportDashboardReport",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 * @Feature("new_reports")
 */
class DashboardReportsController extends CrudController
{
    public static $entity       = ReportDashboardReport::class;
    public static $type         = ReportDashboardReportType::class;
    public static $listPaginate = false;

    public static $sortOptions = [
        'id'         => 'id',
        'sort_order' => 'sort_order',
        'title'      => 'title',
    ];

    public static $listSort  = 'sort_order';
    public static $listOrder = 'asc';

    /**
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(
            array_merge($params, $masterRequest->query->all()),
            null,
            ['_controller' => 'ReportBundle:Api\DashboardReports:list']
        );
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @Rest\Post("/{report}/clone", requirements={"report"="\d+"})
     *
     * @param ReportDashboardReport $report
     * @param Request               $request
     *
     * @return View
     */
    public function cloneAction(ReportDashboardReport $report, Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, $this->getPermissionGroupEntityContext($report->getId(), $request));

        $cloneReport = $this->get('reports.dashboard_manager')->cloneReport($report);

        $this->getManager()->persist($cloneReport);
        $this->getManager()->flush();

        return new View($this->wrap($cloneReport), Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get("/{id}/widgets")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function getReportsAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, $this->getPermissionGroupEntityContext($id, $request));

        return DashboardReportWidgetsController::subRequestSearch($this->getKernel(), $request, [
            'report' => $id,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        if (!$this->getUser()->isAdmin() && !$this->getUser()->can_reports) {
            $qb
                ->join("$alias.dashboard", 'd')
                ->join('d.permissions', 'p')
                ->andWhere('p.person IN (:person) OR p.team IN (:teams) OR d.person IN (:person)')
                ->orWhere('p.person IS NULL AND p.team IS NULL AND p.department IS NULL')
                ->setParameter('teams', $this->getUser()->getTeams())
                ->setParameter('person', $this->getUser());
        }

        if ($request->get('dashboard')) {
            $qb->andWhere("$alias.dashboard = :dashboard");
            $qb->setParameter('dashboard', $request->get('dashboard'));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applySorting(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applySorting($qb, $alias, $request);
        $qb->addOrderBy($alias.'.title', 'asc');
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
     * @Rest\Post("/{report}/variables", requirements={"report"="\d+"})
     *
     * @param ReportDashboardReport $report
     * @param Request               $request
     *Column.php
     *
     * @return View
     */
    public function saveReportVariablesAction(ReportDashboardReport $report, Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, $this->getPermissionGroupEntityContext($report->getId(), $request));

        $vars       = $request->request->get('variables');
        $saveAsPref = $request->request->getBoolean('saveForCurrentAgent');

        if ($saveAsPref) {

            /** @var Person $agent */
            $agent = $this->getUser();

            $pref = $agent->setPreference("reports.dashboards.report.{$report->getId()}.vars", $vars);
            $this->getManager()->persist($pref);
            $this->getManager()->flush();
        } else {
            $report->setVariables($vars);
            $this->getManager()->persist($report);
            $this->getManager()->flush();
        }

        $route = 'deskpro_api_reports_dashboardreports_savereportvariables';
        $view  = View::create(null, Response::HTTP_NO_CONTENT);
        $view->setLocation(
            $this->generateUrl($route, ['report' => $report->getId()])
        );

        return $view;
    }
}

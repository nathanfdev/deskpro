<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Reports;

use Application\DeskPRO\Entity\ReportDashboardWidget;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Type\Reports\ReportDashboardWidgetType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\ReportsRendererInterface;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ReportDashboardWidgetsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/dashboard_report_widgets")
 * @ApiDoc(target="all", section="Reports", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Reports\ReportDashboardWidget")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Reports\ReportDashboardWidgetType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ReportDashboardWidget",
 *          "report"="Application\DeskPRO\Entity\ReportDashboardReport"
 *      }
 *     }
 * )
 * @Feature("new_reports")
 */
class DashboardReportWidgetsController extends CrudController
{
    public static $entity       = ReportDashboardWidget::class;
    public static $type         = ReportDashboardWidgetType::class;
    public static $listPaginate = false;

    /**
     * @Rest\Get("/{dashboardWidget}/download/{type}")
     *
     * @param ReportDashboardWidget $dashboardWidget
     * @param string                $type
     * @param Request               $request
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     * @throws \Exception
     *
     * @return Response
     */
    public function downloadAction(ReportDashboardWidget $dashboardWidget, $type, Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, $this->getPermissionGroupEntityContext($dashboardWidget->getId(), $request));

        $widget = $dashboardWidget->getWidget();
        $query  = $widget->getQuery();

        $variables       = [];
        $reportVariables = $dashboardWidget->getReport()->getVariables();
        $widgetVariables = $dashboardWidget->getVariables();

        foreach ($widgetVariables as $widgetVariable) {
            foreach ($reportVariables as $reportVariable) {
                if ($reportVariable['name'] === $widgetVariable['name']) {
                    $widgetVariable['value'] = $reportVariable['value'];
                }
            }

            $variables[] = $widgetVariable;
        }

        $results = $this->getContainer()->get('reports.dashboard_widget.service')->doRender(
            $query,
            ['variables' => $variables],
            ReportsRendererInterface::TYPE_TABLE,
            $type,
            $this->getUser()
        );
        $renderer = $this->get('reports.renderer_registry')->getRenderer(ReportsRendererInterface::TYPE_TABLE, $type);

        $response = new Response();
        $response->headers->set('Content-Type', $renderer->getContentType());
        $response->headers->set('Content-Disposition', 'inline; filename='.$widget->getTitle().'.'.$renderer->getExtension());
        $response->setContent($results);

        return $response;
    }

    /**
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @throws \Exception
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(
            array_merge($params, $masterRequest->query->all()),
            null,
            ['_controller' => 'ApiBundle:Reports\DashboardReportWidgets:list']
        );
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
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
            ->join("$alias.report", 'r')
            ->join('r.dashboard', 'd')
            ->join('d.permissions', 'p')
            ->andWhere('p.person IN (:person) OR p.team IN (:teams) OR d.person IN (:person)')
            ->orWhere('p.person IS NULL AND p.team IS NULL AND p.department IS NULL')
            ->setParameter('person', $this->getUser())
            ->setParameter('teams', $this->getUser()->getTeams())
        ;

        if ($request->get('report')) {
            $qb->andWhere("$alias.report = :report");
            $qb->setParameter('report', $request->get('report'));
        }
    }
}

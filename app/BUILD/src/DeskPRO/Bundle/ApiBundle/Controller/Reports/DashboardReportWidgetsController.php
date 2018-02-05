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

use Application\DeskPRO\Entity\ReportDashboardWidget;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Type\Reports\ReportDashboardWidgetType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
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

        $query    = $this->get('dpql.compiler')->compile($query, ['variables' => $variables]);
        $results  = $query->getResults();
        $renderer = $this->get('reports.renderer_registry')->getRenderer(ReportDashboardWidget::TYPE_TABLE, $type);

        $response = new Response();
        $response->headers->set('Content-Type', $renderer->getContentType());
        $response->headers->set('Content-Disposition', 'inline; filename='.$widget->getTitle().'.'.$renderer->getExtension());
        $response->setContent($renderer->render($results));

        return $response;
    }

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
            ->leftJoin('d.permissions', 'p')
            ->andWhere('p.person IN (:person) OR p.team IN (:teams) OR d.person IN (:person)')
            ->setParameter('person', $this->getUser())
            ->setParameter('teams', $this->getUser()->getTeams())
        ;

        if ($request->get('report')) {
            $qb->andWhere("$alias.report = :report");
            $qb->setParameter('report', $request->get('report'));
        }
    }
}

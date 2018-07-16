<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Reports;

use Application\DeskPRO\Entity\ReportDashboardReport;
use Application\DeskPRO\Entity\ReportDashboardShareableLink;
use Application\DeskPRO\Entity\ReportDashboardWidget;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DashboardViewController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 * @Rest\Route("/dashboard_view/{shareableLink}", requirements={"shareableLink"="\w+"})
 * @ParamConverter(name="shareableLink", converter="dashboard_shareable_link")
 *
 * @Feature("new_reports")
 */
class DashboardViewController extends BaseController
{
    /**
     * @Rest\Get(")
     *
     * @param ReportDashboardShareableLink $shareableLink
     *
     * @return View
     */
    public function getDashboardAction(ReportDashboardShareableLink $shareableLink)
    {
        return new View($this->wrap($shareableLink->getDashboard()));
    }

    /**
     * @Rest\Get("/reports")
     *
     * @param ReportDashboardShareableLink $shareableLink
     *
     * @return View
     */
    public function getReportsAction(ReportDashboardShareableLink $shareableLink)
    {
        return new View($this->wrap($shareableLink->getDashboard()->getReports()));
    }

    /**
     * @Rest\Get("/reports/{report}/widgets", requirements={"report"="\d+"})
     *
     * @param ReportDashboardShareableLink $shareableLink
     * @param ReportDashboardReport        $report
     * @param Request                      $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getWidgetsAction(ReportDashboardShareableLink $shareableLink, ReportDashboardReport $report, Request $request)
    {
        if ($report->getDashboard() !== $shareableLink->getDashboard()) {
            throw $this->createNotFoundException();
        }

        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('e')
            ->from(ReportDashboardWidget::class, 'e')
            ->where('e.report = :report')
            ->setParameter('report', $report)
        ;

        $ids = $request->get('ids');
        if ($ids) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $ids = array_map(function ($id) {
                return (int) $id;
            }, $ids);

            $qb->andWhere('e.id IN (:ids)');
            $qb->setParameter('ids', $ids);
        }

        $widgets = $qb->getQuery()->getResult();

        return new View($this->wrap($widgets));
    }
}

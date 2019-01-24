<?php

namespace Application\ReportsInterfaceBundle\Controller;

use Application\DeskPRO\Entity\ReportDashboardShareableLink;
use Application\DeskPRO\Entity\SavedDashboardReport;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Component\Util\IpUtils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HeadlessController.
 */
class HeadlessController extends \Application\DeskPRO\Controller\AbstractController
{
    /**
     * @param int    $id
     * @param string $authcode
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($id, $authcode)
    {
        $entityRepository = $this->em->getRepository(SavedDashboardReport::class);
        /** @var SavedDashboardReport $report */
        $report = $entityRepository->findOneBy(['id' => $id, 'authcode' => $authcode]);

        if (!$report) {
            throw $this->createNotFoundException();
        }
        $widgetService = $this->get('reports.dashboard_widget.service');
        $widgets       = [];
        foreach ($report->getSavedWidgets() as $savedWidget) {
            $wdata                = $widgetService->getWidgetData($savedWidget);
            $wdata['id']          = $savedWidget->getDashboardWidget()->getId();
            $wdata['widget_type'] = $widgetService->getWidgetType($savedWidget->getType());
            $wdata['type']        = $savedWidget->getType();
            $wdata['data']        = $savedWidget->getData() ?: null;
            $widgets[]            = $wdata;
        }

        $reportPdfGenerator = $this->getContainer()->get('reports.report_pdf_generator');

        return $this->render('ReportsInterfaceBundle:Headless:headless.html.twig', [
            'report' => [
                'title'        => $report->getTitle(),
                'widgets'      => $widgets,
                'date_created' => $report->getDateCreated()->format($this->container->getSetting('core.date_fulltime')),
            ],
            'printConfig' => $reportPdfGenerator->calculatePrintConfig($report),
        ]);
    }

    /**
     * @param string  $authcode
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dashboardAction($authcode, Request $request)
    {
        $link = $this->em->getRepository(ReportDashboardShareableLink::class)->findOneBy([
            'authCode' => $authcode,
        ]);

        if (!$link) {
            return $this->render('ReportsInterfaceBundle:Headless:headless-dashboard-error.html.twig', [
                'error_message' => 'Dashboard not found.',
            ]);
        }

        // check whitelist permissions
        if ($link->getWhoCanUse() === ReportDashboardShareableLink::USE_WHITELIST
            && !IpUtils::checkIp($request->getClientIp(), $link->getIpWhitelist())) {
            return $this->render('ReportsInterfaceBundle:Headless:headless-dashboard-error.html.twig', [
                'error_message' => 'Access denied.',
            ]);
        }

        $dashboard = $link->getDashboard();

        $context = new SideloadSerializationContext();
        $context->setInlineSideloads(true);
        $context->setIncludes(['reports']);

        $serializedDashboard = $this->container->get('serializer')->toArray(new ApiWrapper($dashboard), $context);
        $serializedDashboard = $serializedDashboard['data'];

        $currentReport = null;
        if ($link->getDefaultReport()) {
            $currentReport = $link->getDefaultReport();
        } else {
            $currentReport = $dashboard->getReports()->first();
        }

        return $this->render('ReportsInterfaceBundle:Headless:headless-dashboard.html.twig', [
            'dashboard'    => $serializedDashboard,
            'report_id'    => $currentReport ? $currentReport->getId() : null,
            'auth_code'    => $authcode,
            'group_params' => $this->container->get('reports.widget.service')->getGroupParams(),
        ]);
    }
}

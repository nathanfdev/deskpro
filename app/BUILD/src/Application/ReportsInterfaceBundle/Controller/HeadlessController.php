<?php

/**
 * DeskPRO.
 */

namespace Application\ReportsInterfaceBundle\Controller;

use Application\DeskPRO\Entity\SavedDashboardReport;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HeadlessController extends \Application\DeskPRO\Controller\AbstractController
{
    /**
     * @param $id
     * @param $authcode
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
            throw new NotFoundHttpException();
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
                'title'   => $report->getTitle(),
                'widgets' => $widgets,
            ],
            'printConfig' => $reportPdfGenerator->calculatePrintConfig($report),
        ]);
    }
}

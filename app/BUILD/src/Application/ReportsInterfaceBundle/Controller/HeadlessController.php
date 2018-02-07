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

/**
 * DeskPRO.
 */

namespace Application\ReportsInterfaceBundle\Controller;

use Application\DeskPRO\Entity\SavedDashboardReport;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HeadlessController extends \Application\DeskPRO\Controller\AbstractController
{
    public function viewAction($id, $authcode)
    {
        $entityRepository = $this->em->getRepository(SavedDashboardReport::class);
        /** @var SavedDashboardReport $report */
        $report = $entityRepository->findOneBy(['id' => $id, 'authcode' => $authcode]);

        if (!$report) {
            throw new NotFoundHttpException();
        }
        $widgetService = $this->get('dashboard.widget.service');
        $widgets       = [];
        foreach ($report->getSavedWidgets() as $savedWidget) {
            $wdata                = $widgetService->getWidgetData($savedWidget);
            $wdata['id']          = $savedWidget->getDashboardWidget()->getId();
            $wdata['widget_type'] = $widgetService->getWidgetType($savedWidget->getType());
            $wdata['type']        = $savedWidget->getType();
            $wdata['data']        = $savedWidget->getData() ?: null;
            $widgets[]            = $wdata;
        }

        return $this->render('ReportsInterfaceBundle:Headless:headless.html.twig', [
            'report' => [
                'title'   => $report->getTitle(),
                'widgets' => $widgets,
            ],
        ]);
    }
}

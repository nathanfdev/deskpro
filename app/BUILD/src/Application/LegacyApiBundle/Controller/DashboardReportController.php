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

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReport;
use Application\LegacyApiBundle\Service\Dashboard as DashboardService;
use Application\LegacyApiBundle\Service\DashboardPermissions as DashboardPermissionService;
use Application\LegacyApiBundle\Service\DashboardWidget as DashboardWidgetService;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\Report\ScheduledReport;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 */
class DashboardReportController extends AbstractController
{
    /** @var DashboardService */
    protected $service;

    /** @var DashboardPermissionService */
    protected $permissionsService;

    /** @var DashboardWidgetService */
    protected $widgetService;

    public function init()
    {
        parent::init();
        $this->service            = $this->get('dashboard.service');
        $this->permissionsService = $this->get('dashboard.permissions.service');
        $this->widgetService      = $this->get('dashboard.widget.service');
    }

    /**
     * @return Response
     */
    public function listAction()
    {
        $data    = [];
        $reports = $this->em->getRepository(DashboardReport::class)->findAll();

        foreach ($reports as $k => $report) {
            /* @var DashboardReport $report */
            $data[$k] = $this->service->getReportData($report);
        }

        return $this->createApiResponse($data);
    }

    /**
     * @param int $id
     * @param int $dashboard_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function cloneAction($id, $dashboard_id)
    {
        $prototype = $this->service->getReport($id);
        $dashboard = $this->service->getDashboard($dashboard_id);
        if (!$this->permissionsService->isEditableDashboard($dashboard)) {
            throw $this->createNotFoundException();
        }
        $report = new DashboardReport();
        $report
            ->setTitle($prototype->getTitle().'_clone')
            ->setDashboard($dashboard)
            ->setSortOrder($this->service->getLastSortOrder($dashboard));
        $this->widgetService->copyWidgetLinks($report, $prototype);

        return $this->createApiSuccessResponse($this->service->saveReport($report, true));
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function saveAction($id)
    {
        $postData = $this->in->getAll('post');

        $report = $this->service->getReport($id);
        if (!$this->permissionsService->isEditableDashboard($report->getDashboard())) {
            throw $this->createNotFoundException();
        }

        $title = $postData['title'];

        $report->setTitle($title);
        foreach ($postData['widgets'] as $widget) {
            $widgetEntity = $this->em->getRepository('DeskPRO:ReportDashboardWidget')->find((int) $widget['id']);
            $widgetEntity->setTitle($widget['title']);
            $this->em->persist($widgetEntity);
        }

        return $this->createApiSuccessResponse($this->service->saveReport($report, true));
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function saveReportVarsAction($id)
    {
        $postVars = $this->in->getArrayValue('variables', 'post');
        $report   = $this->service->getReport($id);
        $vars     = [];
        foreach ($postVars as $postVar) {
            $vars[$postVar['name']] = $postVar;
        }
        $report->setVariables($vars);

        return $this->createApiSuccessResponse($this->service->saveReport($report, true));
    }

    /**
     * @param $dashboard_id
     *
     * @return Response
     */
    public function createAction($dashboard_id)
    {
        $dashboard = $this->service->getDashboard($dashboard_id);
        if (!$this->permissionsService->isEditableDashboard($dashboard)) {
            throw $this->createNotFoundException();
        }
        $report = new DashboardReport();
        $title  = $this->in->getCleanValue('title', 'string');

        $report
            ->setTitle($title)
            ->setDashboard($dashboard)
            ->setSortOrder($this->service->getLastSortOrder($dashboard));

        return $this->createApiSuccessResponse($this->service->saveReport($report, true));
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $report = $this->service->getReport($id);
        if (!$this->permissionsService->isEditableDashboard($report->getDashboard())) {
            throw $this->createNotFoundException();
        }
        $this->em->remove($report);
        $this->em->flush();

        return $this->createApiDeleteResponse();
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        $report         = $this->service->getReport($id);
        $data           = $this->service->getReportData($report);
        $data['loaded'] = true;

        return $this->createApiResponse($data);
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     *
     * @return Response
     */
    public function scheduleAction($id)
    {
        $report = $this->service->getReport($id);

        $scheduledReport = $this->em
            ->getRepository(ScheduledReport::class)
            ->findOneBy([
                'person' => $this->person,
                'report' => $id,
            ]);
        if (!$scheduledReport) {
            $scheduledReport = new ScheduledReport();
        }

        $this->in->getAll('post');
        $whenTz      = $this->person->getTimezone();
        $whenSetting = $this->in->getArrayValue('when');
        $frequency   = $this->in->getString('frequency');
        $reportSaver = $this->getContainer()->get('deskpro.reports.saver');
        $date        = $reportSaver->calculateNextSendDate($whenSetting, $whenTz, $frequency);
        $scheduledReport
            ->setReport($report)
            ->setFrequency($frequency)
            ->setPerson($this->person)
            ->setWhenSetting($whenSetting)
            ->setWhenTz($whenTz)
            ->setNextSendDate($date)
            ->setSendTo($this->in->getArrayValue('sendTo'));
        $this->em->persist($scheduledReport);
        $this->em->flush();

        return $this->createApiSuccessResponse();
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function getScheduledReportAction($id)
    {
        $scheduledReport = $this->em
            ->getRepository(ScheduledReport::class)
            ->findOneBy([
                'person' => $this->person,
                'report' => $id,
            ]);
        if (!$scheduledReport) {
            throw new NotFoundHttpException();
        }

        // just a stub until it goes to apiv2
        $data = [
            'id'        => $scheduledReport->getId(),
            'when'      => $scheduledReport->getWhenSetting(),
            'frequency' => $scheduledReport->getFrequency(),
            'sendTo'    => $scheduledReport->getSendTo(),
        ];

        return $this->createApiResponse($data);
    }
}

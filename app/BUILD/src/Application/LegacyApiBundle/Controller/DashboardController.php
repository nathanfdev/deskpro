<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\ReportDashboard as Dashboard;
use Application\DeskPRO\Entity\ReportDashboardReport as Tab;
use Application\LegacyApiBundle\Service\Dashboard as DashboardService;
use Application\LegacyApiBundle\Service\DashboardPermissions as DashboardPermissionService;
use Application\LegacyApiBundle\Service\DashboardWidget as DashboardWidgetService;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 */
class DashboardController extends AbstractController
{
    /** @var DashboardService */
    protected $service;

    /** @var DashboardPermissionService */
    protected $permissionsService;

    /** @var DashboardWidgetService */
    protected $widgetService;

    /**
     * {@inherited}.
     */
    public function init()
    {
        parent::init();
        $this->service            = $this->get('dashboard.service');
        $this->widgetService      = $this->get('dashboard.service');
        $this->permissionsService = $this->get('dashboard.permissions.service');
        $this->widgetService      = $this->get('dashboard.widget.service');
    }

    public function listAction()
    {
        $data       = [];
        $dashboards = $this->em->getRepository(Dashboard::class)->findAll();
        foreach ($dashboards as $k => $dashboard) {
            /** @var Dashboard $dashboard */
            if ($this->permissionsService->isAllowedToView($this->person, $dashboard)) {
                $data[$k] = $this->service->getDashboardData($dashboard);
            }
        }

        return $this->createApiResponse($data);
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        $dashboard = $this->service->getDashboard($id);
        if (!$this->permissionsService->isAllowedToView($this->person, $dashboard)) {
            throw $this->createNotFoundException('Dashboard not found!');
        }
        $data                = $this->service->getDashboardData($dashboard);
        $data['loaded']      = true;
        $data['reports']     = $this->service->getReportsData($dashboard);
        $data['permissions'] = $this->permissionsService->getApiDashboardPermissions($dashboard);

        return $this->createApiResponse($data);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function saveAction($id)
    {
        if ($id) {
            $dashboard = $this->service->getDashboard($id);

            if (!$this->permissionsService->isAllowedToEdit($this->person, $dashboard)) {
                throw $this->createNotFoundException('Dashboard not found!');
            }
        } else {
            $dashboard = new Dashboard();
        }
        // We have to save only permissions here if dashboard is not editable
        $postData = $this->in->getAll('post');
        if ($this->permissionsService->isEditableDashboard($dashboard)) {
            $dashboard->setTitle($postData['title']);
            $dbReports     = $dashboard->getReports();
            $apiReports    = [];
            $apiReportsIds = [];
            foreach ($postData['reports'] as $report) {
                $reportEntity = false;
                if (
                 isset($report['isAdded']) &&
                 $report['isAdded'] === true
                ) {
                    $reportEntity = new Tab();
                    $reportEntity->setColumns(10)
                                 ->setDashboard($dashboard);
                    $dashboard->addReport($reportEntity);
                    if (isset($report['cloneId']) && (int) $report['cloneId'] > 0) {
                        $report_prototype = $this->service->getReport($report['cloneId']);
                        $this->widgetService->copyWidgetLinks($reportEntity, $report_prototype);
                    }
                } elseif ((int) $report['id'] > 0) {
                    $reportEntity    = $this->service->getReport($report['id']);
                    $apiReportsIds[] = (int) $report['id'];
                }
                if ($reportEntity) {
                    $reportEntity->setTitle($report['title']);
                    $apiReports[] = $reportEntity;
                }
            }
            foreach ($dbReports as $dbReport) {
                if ($dbReport->getId() && !in_array($dbReport->getId(), $apiReportsIds)) {
                    $dashboard->removeReport($dbReport);
                    $this->service->deleteReport($dbReport, false);
                }
            }
            /**
             * @var 
             * @var Tab $apiReport
             */
            foreach ($apiReports as $index => $apiReport) {
                $apiReport->setSortOrder($index + 1);
                $this->service->saveReport($apiReport);
            }
        }
        $returnData            = $this->service->saveDashboard($dashboard);
        $returnData['reports'] = $this->service->getReportsData($dashboard);
        if (isset($postData['permissions'])) {
            foreach ($postData['permissions'] as $permission) {
                $agent = $this->permissionsService->getAgent($permission['id']);
                if ($agent->getId() == $this->person->getId()) {
                    $this->permissionsService->setPermissions($agent, $dashboard, DashboardPermissionService::PERMISSION_FULL);
                } else {
                    $this->permissionsService->setPermissions($agent, $dashboard, $permission['permissions']);
                }
            }
        }

        $returnData['permissions'] = $this->permissionsService->getApiDashboardPermissions($dashboard);

        return $this->createApiSuccessResponse($returnData);
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function cloneAction($id)
    {
        $prototype = $this->service->getDashboard($id);
        if (!$this->permissionsService->isAllowedToView($this->person, $prototype)) {
            throw $this->createNotFoundException('Dashboard not found!');
        }
        $dashboard = new Dashboard();
        $dashboard->setTitle($prototype->getTitle().'_clone');

        foreach ($prototype->getReports() as $report_prototype) {
            $report = new Tab();
            $report
                ->setTitle($report_prototype->getTitle().'_clone')
                ->setColumns($report_prototype->getColumns())
                ->setSortOrder($report_prototype->getSortOrder());
            $dashboard->addReport($report);
            $this->widgetService->copyWidgetLinks($report, $report_prototype);
        }
        $data = $this->service->saveDashboard($dashboard);

        $this->permissionsService->clonePermissions($dashboard, $prototype);

        return $this->createApiResponse($data);
    }

    /**
     * @param $id
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $dashboard = $this->service->getDashboard($id);
        if (
            !$this->permissionsService->isAllowedToEdit($this->person, $dashboard)
            ||
            !$this->permissionsService->isEditableDashboard($dashboard)
        ) {
            throw $this->createNotFoundException('Dashboard not found!');
        }
        $this->service->deleteDashboard($dashboard);

        return $this->createApiDeleteResponse();
    }
}

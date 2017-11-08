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

use Application\DeskPRO\Entity\Person;
use Application\LegacyApiBundle\Service\Dashboard as DashboardService;
use Application\LegacyApiBundle\Service\DashboardPermissions as DashboardPermissionsService;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ApiModes("all")
 */
class DashboardPermissionsController extends AbstractController
{
    /** @var DashboardPermissionsService */
    protected $permissionsService;

    /** @var DashboardService */
    protected $service;

    public function init()
    {
        parent::init();
        $this->service            = $this->get('dashboard.service');
        $this->permissionsService = $this->get('dashboard.permissions.service');
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function listAction($id = 0)
    {
        if ($id > 0) {
            $dashboard = $this->service->getDashboard($id);
            if (!$this->permissionsService->isAllowedToView($this->person, $dashboard)) {
                throw $this->createNotFoundException('Dashboard not found!');
            }
            $permissions = $this->permissionsService->getApiDashboardPermissions($dashboard);
        } else {
            $permissions = $this->permissionsService->getNewDashboardPermissions();
            foreach ($permissions as &$permission) {
                if ($permission['id'] == $this->person->getId()) {
                    $permission['permissions'] = DashboardPermissionsService::PERMISSION_FULL;
                }
            }
        }

        return $this->createApiResponse($permissions);
    }

    public function saveAction($id)
    {
        $dashboard = $this->service->getDashboard($id);
        $agent_id  = $this->in->getCleanValue('agent_id', 'integer');
        /** @var Person $agent */
        $agent       = $this->em->getRepository('DeskPRO:Person')->find($agent_id);
        $permissions = $this->in->getCleanValue('permissions', 'integer');
        $this->permissionsService->setPermissions($agent, $dashboard, $permissions);

        return $this->createApiSuccessResponse();
    }
}

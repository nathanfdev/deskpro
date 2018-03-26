<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\LoginLogs\LoginLogs;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class LoginLogsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction($agent_id = null)
    {
        $page = $this->in->getUint('page');

        $login_logs = new LoginLogs($this->em, $this->container->getAgentData());
        $login_logs->setPage($page);

        if ($agent_id) {
            if ($agent_filter = $this->container->getAgentData()->get($agent_id)) {
                $login_logs->setFilter($agent_filter);
            } else {
                throw $this->createNotFoundException("Unknown agent: $agent_id");
            }
        }

        $returnedData['page']      = $page;
        $returnedData['logs']      = $login_logs->getAll();
        $returnedData['num_pages'] = $login_logs->getPageCount();

        return $this->createApiResponse([
            'login_logs' => $returnedData,
        ]);
    }
}
